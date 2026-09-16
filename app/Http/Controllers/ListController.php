<?php

namespace App\Http\Controllers;

use App\Models\TaskList;
use App\Models\TodoList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ListController extends Controller
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
        ->where('user_id', '!=', $currentUser->id)
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
     * Menyimpan list baru ke dalam database secara atomik (SRS-03).
     * Pembuatan TaskList baru + pendaftaran pembuat sebagai owner di list_members dalam 1 transaksi atomik.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required_without:title|string|max:255',
            'title'       => 'required_without:name|string|max:255',
            'description' => 'nullable|string',
        ]);

        $name = $validated['name'] ?? $validated['title'];

        $list = DB::transaction(function () use ($name, $validated, $request) {
            $newList = TaskList::create([
                'title'       => $name,
                'name'        => $name,
                'user_id'     => $request->user()->id,
                'owner_id'    => $request->user()->id,
                'description' => $validated['description'] ?? null,
            ]);

            // Daftarkan pembuat sebagai owner di tabel pivot list_members (list_user)
            $newList->members()->attach($request->user()->id, [
                'role' => 'owner',
            ]);

            return $newList;
        });

        return redirect()->route('lists.show', $list)
            ->with('success', 'Daftar tugas (list) berhasil dibuat!');
    }

    /**
     * Menampilkan detail dari suatu list beserta anggotanya.
     * (Catatan: Sesuai SRS PM, method show tetap dikunci dan tidak diubah).
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
            'name'        => 'required_without:title|string|max:255',
            'title'       => 'required_without:name|string|max:255',
            'description' => 'nullable|string',
        ]);

        $name = $validated['name'] ?? $validated['title'];

        $list->update([
            'title'       => $name,
            'name'        => $name,
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('lists.show', $list)
            ->with('success', 'Informasi list berhasil diperbarui!');
    }

    /**
     * Menghapus list dari database secara atomik (SRS-03).
     * Otorisasi: hanya owner yang berhak menghapus.
     * Penghapusan TaskList + seluruh tasks + list_members wajib dalam 1 transaksi atomik.
     */
    public function destroy(Request $request, TodoList $list)
    {
        abort_if($list->owner_id !== $request->user()->id, 403, 'Hanya pemilik (owner) yang dapat menghapus list ini.');

        DB::transaction(function () use ($list) {
            $list->tasks()->delete();
            $list->members()->detach();
            $list->delete();
        });

        return redirect()->route('lists.index')
            ->with('success', 'List berhasil dihapus.');
    }
}
