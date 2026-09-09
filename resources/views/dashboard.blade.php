@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-sm text-gray-500">Selamat datang kembali, {{ auth()->user()->name }}.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <a href="{{ route('lists.index') }}" class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition p-6 block">
            <h2 class="font-bold text-gray-900">📋 Daftar List</h2>
            <p class="text-sm text-gray-600 mt-1">Lihat dan kelola semua list tugas pribadi maupun kolaborasi.</p>
        </a>

        <a href="{{ route('lists.create') }}" class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition p-6 block">
            <h2 class="font-bold text-gray-900">➕ Buat List Baru</h2>
            <p class="text-sm text-gray-600 mt-1">Mulai daftar tugas baru untuk proyek atau sprint Anda.</p>
        </a>

        @if(auth()->user()->role === 'admin')
            <a href="{{ route('admin.users.index') }}" class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition p-6 block">
                <h2 class="font-bold text-gray-900">👤 Kelola User</h2>
                <p class="text-sm text-gray-600 mt-1">Tambah atau hapus akun pengguna aplikasi.</p>
            </a>
        @endif
    </div>
</div>
@endsection
