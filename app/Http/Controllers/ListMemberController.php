<?php

namespace App\Http\Controllers;

use App\Models\TodoList;
use App\Models\User;
use Illuminate\Http\Request;

class ListMemberController extends Controller
{
    /**
     * Menambahkan anggota tim baru ke dalam list.
     */
    public function store(Request $request, TodoList $list)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'Email anggota wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.exists' => 'Pengguna dengan email tersebut tidak ditemukan di sistem.',
        ]);

        $user = User::where('email', $validated['email'])->first();

        // Validasi: Owner tidak bisa ditambahkan sebagai anggota
        if ($list->isOwner($user)) {
            return redirect()->route('lists.show', $list)
                ->with('error', 'Anda adalah pemilik (owner) list ini, tidak perlu menambahkan diri sendiri sebagai anggota.');
        }

        // Validasi: Pengguna sudah menjadi anggota
        if ($list->isMember($user)) {
            return redirect()->route('lists.show', $list)
                ->with('error', 'Pengguna tersebut sudah menjadi anggota dari list ini.');
        }

        $list->members()->syncWithoutDetaching([$user->id]);

        return redirect()->route('lists.show', $list)
            ->with('success', 'Anggota ' . $user->name . ' berhasil ditambahkan ke list!');
    }

    /**
     * Mengeluarkan anggota dari list.
     */
    public function destroy(TodoList $list, User $user)
    {
        // Cegah pengeluaran jika user yang dituju adalah owner
        if ($list->isOwner($user)) {
            return redirect()->route('lists.show', $list)
                ->with('error', 'Pemilik (owner) list tidak dapat dikeluarkan.');
        }

        $list->members()->detach($user->id);

        return redirect()->route('lists.show', $list)
            ->with('success', 'Anggota ' . $user->name . ' berhasil dikeluarkan dari list.');
    }
}
