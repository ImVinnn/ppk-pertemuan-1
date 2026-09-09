<?php

namespace App\Http\Controllers;

use App\Models\TodoList;
use Illuminate\Http\Request;

class TodoListController extends Controller
{
    /**
     * Menampilkan daftar list pribadi dan list kolaborasi tim.
     */
    public function index(Request $request)
    {
        $currentUser = $request->user();

        $ownedLists = TodoList::where('user_id', $currentUser->id)
            ->with(['owner', 'members'])
            ->latest()
            ->get();

        $collaboratedLists = TodoList::whereHas('members', function ($query) use ($currentUser) {
            $query->where('users.id', $currentUser->id);
        })
        ->with(['owner', 'members'])
        ->latest()
        ->get();

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

        $list = TodoList::create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('lists.show', $list)
            ->with('success', 'Daftar tugas (list) berhasil dibuat!');
    }

    /**
     * Menampilkan detail dari suatu list beserta anggotanya.
     */
    public function show(Request $request, TodoList $list)
    {
        // Otorisasi: Hanya Owner dan Anggota yang berhak melihat detail list
        abort_unless($list->hasAccess($request->user()), 403, 'Anda tidak memiliki hak akses untuk melihat list ini.');

        $list->load(['owner', 'members', 'tasks']);

        $isOwner = $list->isOwner($request->user());
        $isMember = $list->isMember($request->user());

        return view('lists.show', compact('list', 'isOwner', 'isMember'));
    }

    /**
     * Menampilkan form edit list.
     */
    public function edit(Request $request, TodoList $list)
    {
        abort_unless($list->isOwner($request->user()), 403, 'Hanya pemilik (owner) yang dapat mengedit list ini.');

        return view('lists.edit', compact('list'));
    }

    /**
     * Memperbarui informasi list di database.
     */
    public function update(Request $request, TodoList $list)
    {
        abort_unless($list->isOwner($request->user()), 403, 'Hanya pemilik (owner) yang dapat memperbarui list ini.');

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
    public function destroy(Request $request, TodoList $list)
    {
        abort_unless($list->isOwner($request->user()), 403, 'Hanya pemilik (owner) yang dapat menghapus list ini.');

        $list->delete();

        return redirect()->route('lists.index')
            ->with('success', 'List berhasil dihapus!');
    }
}
