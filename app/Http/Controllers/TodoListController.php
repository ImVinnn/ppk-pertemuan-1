<?php

namespace App\Http\Controllers;

use App\Models\TodoList;
use App\Models\User;
use Illuminate\Http\Request;

class TodoListController extends Controller
{
    /**
     * Menampilkan daftar list pribadi dan list kolaborasi tim.
     */
    public function index()
    {
        $currentUser = auth()->user() ?? User::first();

        if (! $currentUser) {
            $ownedLists = collect();
            $collaboratedLists = collect();
        } else {
            // List milik sendiri
            $ownedLists = TodoList::where('user_id', $currentUser->id)
                ->with(['owner', 'members'])
                ->latest()
                ->get();

            // List di mana user ini adalah anggota tim
            $collaboratedLists = TodoList::whereHas('members', function ($query) use ($currentUser) {
                $query->where('users.id', $currentUser->id);
            })
            ->with(['owner', 'members'])
            ->latest()
            ->get();
        }

        return view('lists.index', compact('ownedLists', 'collaboratedLists'));
    }

    /**
     * Menampilkan form pembuatan list baru.
     */
    public function create()
    {
        return view('lists.create');
    }

    /**
     * Menyimpan list baru ke dalam database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Gunakan user yang login, atau fallback ke user pertama untuk kebutuhan demo
        $user = auth()->user() ?? User::firstOrCreate(
            ['email' => 'demo@example.com'],
            ['name' => 'Demo User', 'password' => bcrypt('password')]
        );

        $list = TodoList::create([
            'user_id' => $user->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('lists.show', $list)
            ->with('success', 'Daftar tugas (list) berhasil dibuat!');
    }

    /**
     * Menampilkan detail dari suatu list beserta anggotanya.
     */
    public function show(TodoList $list)
    {
        $currentUser = auth()->user() ?? User::first();

        // Otorisasi: Hanya Owner dan Anggota yang berhak melihat detail list
        if ($currentUser && ! $list->hasAccess($currentUser)) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat list ini.');
        }

        $list->load(['owner', 'members']);

        $isOwner = $currentUser ? $list->isOwner($currentUser) : true;
        $isMember = $currentUser ? $list->isMember($currentUser) : false;

        return view('lists.show', compact('list', 'isOwner', 'isMember'));
    }

    /**
     * Menampilkan form edit list.
     */
    public function edit(TodoList $list)
    {
        $currentUser = auth()->user() ?? User::first();

        // Otorisasi: Hanya Owner yang dapat mengedit list
        if ($currentUser && ! $list->isOwner($currentUser)) {
            abort(403, 'Hanya pemilik (owner) yang memiliki hak akses untuk mengedit list ini.');
        }

        return view('lists.edit', compact('list'));
    }

    /**
     * Memperbarui informasi list di database.
     */
    public function update(Request $request, TodoList $list)
    {
        $currentUser = auth()->user() ?? User::first();

        // Otorisasi: Hanya Owner yang dapat memperbarui list
        if ($currentUser && ! $list->isOwner($currentUser)) {
            abort(403, 'Hanya pemilik (owner) yang memiliki hak akses untuk memperbarui list ini.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $list->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('lists.show', $list)
            ->with('success', 'Informasi list berhasil diperbarui!');
    }

    /**
     * Menghapus list dari database.
     */
    public function destroy(TodoList $list)
    {
        $currentUser = auth()->user() ?? User::first();

        // Otorisasi: Hanya Owner yang dapat menghapus list
        if ($currentUser && ! $list->isOwner($currentUser)) {
            abort(403, 'Hanya pemilik (owner) yang memiliki hak akses untuk menghapus list ini.');
        }

        $list->delete();

        return redirect()->route('lists.index')
            ->with('success', 'List berhasil dihapus!');
    }
}
