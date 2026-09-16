@extends('layouts.app')

@section('title', $list->title)

@section('content')
<div class="space-y-6">
    <!-- Header Navigasi -->
    <div class="flex items-center justify-between">
        <a href="{{ route('lists.index') }}" class="text-sm font-medium text-indigo-600 hover:underline flex items-center gap-1">
            &larr; Kembali ke Daftar List
        </a>

        @if($isOwner)
            <div class="flex items-center gap-2">
                <a href="{{ route('lists.edit', $list) }}" class="px-3 py-1.5 text-xs font-semibold bg-amber-500 hover:bg-amber-600 text-white rounded-md transition shadow-sm">
                    ✏️ Edit List
                </a>
                <form action="{{ route('lists.destroy', $list) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus list ini beserta seluruh tugas di dalamnya?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 text-xs font-semibold bg-red-600 hover:bg-red-700 text-white rounded-md transition shadow-sm">
                        🗑️ Hapus List
                    </button>
                </form>
            </div>
        @endif
    </div>

    <!-- Kartu Informasi List & Progress -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Detail List</span>
                <h1 class="text-2xl font-bold text-gray-900">{{ $list->title }}</h1>
            </div>
            <div>
                @if($isOwner)
                    <span class="bg-yellow-100 text-yellow-800 text-xs font-bold px-3 py-1 rounded-full">👑 Anda adalah Owner</span>
                @else
                    <span class="bg-emerald-100 text-emerald-800 text-xs font-bold px-3 py-1 rounded-full">🤝 Anda sebagai Anggota</span>
                @endif
            </div>
        </div>

        <p class="text-gray-600 text-sm">
            {{ $list->description ?? 'Tidak ada deskripsi pada list ini.' }}
        </p>
    </div>

    <!-- Grid Informasi Anggota & Tugas -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Kolom Kiri (2 Kolom): Modul Task -->
        <div class="lg:col-span-2 space-y-4">
            @include('lists.partials.tasks', ['tasks' => $list->tasks])
        </div>

        <!-- Kolom Kanan (1 Kolom): Pemilik & Anggota Tim (SRS-04) -->
        <div class="space-y-4">
            @include('lists.partials.members')
        </div>
    </div>
</div>
@endsection
