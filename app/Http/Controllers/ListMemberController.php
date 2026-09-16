<?php

namespace App\Http\Controllers;

use App\Models\TodoList;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ListMemberController extends Controller
{
    /**
     * Menambahkan anggota tim baru ke dalam list (SRS-04).
     * Otorisasi: hanya owner yang berhak menambah anggota.
     */
    public function store(Request $request, TodoList $list)
    {
        // Otorisasi: Hanya Owner yang dapat menambah anggota ke list
        abort_if($list->owner_id !== $request->user()->id, 403, 'Hanya pemilik (owner) yang dapat menambahkan anggota ke list ini.');

        $validated = $request->validate([
            'user_id' => 'required_without:email|nullable|exists:users,id',
            'email'   => 'required_without:user_id|nullable|email|exists:users,email',
        ], [
            'user_id.exists' => 'Pengguna yang dipilih tidak valid.',
            'email.exists'   => 'Pengguna dengan email tersebut tidak ditemukan di sistem.',
        ]);

        $user = ! empty($validated['user_id'])
            ? User::findOrFail($validated['user_id'])
            : User::where('email', $validated['email'])->firstOrFail();

        // Validasi: Owner tidak bisa ditambahkan sebagai anggota biasa
        if ($list->isOwner($user)) {
            return redirect()->route('lists.show', $list)
                ->with('error', 'Anda adalah pemilik (owner) list ini, tidak perlu menambahkan diri sendiri sebagai anggota.');
        }

        // Validasi: Pengguna sudah menjadi anggota
        if ($list->isMember($user)) {
            return redirect()->route('lists.show', $list)
                ->with('error', 'Pengguna tersebut sudah menjadi anggota dari list ini.');
        }

        DB::transaction(function () use ($list, $user) {
            $list->members()->syncWithoutDetaching([
                $user->id => ['role' => 'member']
            ]);
        });

        return redirect()->route('lists.show', $list)
            ->with('success', 'Anggota ' . $user->name . ' berhasil ditambahkan ke list!');
    }

    /**
     * Mengeluarkan anggota dari list (SRS-04).
     * Otorisasi: hanya owner yang berhak mengeluarkan anggota.
     */
    public function destroy(Request $request, TodoList $list, User $user)
    {
        // Otorisasi: Hanya Owner yang dapat mengeluarkan anggota dari list
        abort_if($list->owner_id !== $request->user()->id, 403, 'Hanya pemilik (owner) yang dapat mengeluarkan anggota dari list ini.');

        // Cegah pengeluaran jika user yang dituju adalah owner
        if ($list->isOwner($user)) {
            return redirect()->route('lists.show', $list)
                ->with('error', 'Pemilik (owner) list tidak dapat dikeluarkan.');
        }

        DB::transaction(function () use ($list, $user) {
            $list->members()->detach($user->id);
        });

        return redirect()->route('lists.show', $list)
            ->with('success', 'Anggota ' . $user->name . ' berhasil dikeluarkan dari list.');
    }
}
