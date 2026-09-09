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
        $list->load(['owner', 'members']);

        $currentUser = auth()->user() ?? User::first();
        $isOwner = $currentUser ? $list->isOwner($currentUser) : true;
        $isMember = $currentUser ? $list->isMember($currentUser) : false;

        return view('lists.show', compact('list', 'isOwner', 'isMember'));
    }

    /**
     * Menampilkan form edit list.
     */
    public function edit(TodoList $list)
    {
        return view('lists.edit', compact('list'));
    }

    /**
     * Memperbarui informasi list di database.
     */
    public function update(Request $request, TodoList $list)
    {
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
        $list->delete();

        return redirect()->route('lists.index')
            ->with('success', 'List berhasil dihapus!');
    }
}
