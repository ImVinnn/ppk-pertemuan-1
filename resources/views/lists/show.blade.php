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

        <!-- Kolom Kanan (1 Kolom): Pemilik & Anggota Tim -->
        <div class="space-y-4">
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 space-y-4">
                <h2 class="text-base font-bold text-gray-800 flex items-center justify-between">
                    <span>👥 Anggota Tim</span>
                    <span class="text-xs font-normal text-gray-500">{{ $list->members->count() + 1 }} Orang</span>
                </h2>

                <!-- Owner -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between p-2 rounded-md bg-yellow-50 border border-yellow-200">
                        <div class="truncate">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ $list->owner->name ?? 'Owner' }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ $list->owner->email ?? 'owner@example.com' }}</p>
                        </div>
                        <span class="text-[10px] font-bold text-yellow-700 bg-yellow-200 px-1.5 py-0.5 rounded">Owner</span>
                    </div>

                    <!-- Anggota Kolaborator -->
                    @foreach($list->members as $member)
                        <div class="flex items-center justify-between p-2 rounded-md bg-gray-50 border border-gray-200">
                            <div class="truncate mr-2">
                                <p class="text-sm font-medium text-gray-800 truncate">{{ $member->name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ $member->email }}</p>
                            </div>
                            <div class="flex items-center gap-1.5 flex-shrink-0">
                                <span class="text-[10px] font-medium text-gray-600 bg-gray-200 px-1.5 py-0.5 rounded">Anggota</span>
                                @if($isOwner)
                                    <form action="{{ route('lists.members.destroy', [$list, $member]) }}" method="POST" onsubmit="return confirm('Keluarkan {{ $member->name }} dari list ini?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-bold p-1 hover:bg-red-50 rounded" title="Keluarkan anggota">
                                            ✕
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    @if($list->members->isEmpty())
                        <p class="text-xs text-gray-400 text-center py-2">Belum ada anggota tim lain.</p>
                    @endif
                </div>

                @if($isOwner)
                    <div class="pt-4 border-t border-gray-100">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">➕ Tambah Anggota</h3>
                        <form action="{{ route('lists.members.store', $list) }}" method="POST" class="space-y-2">
                            @csrf
                            <div>
                                <input type="email" name="email" required placeholder="Masukkan email anggota..."
                                    class="w-full text-xs rounded-md border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            </div>
                            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold py-2 px-3 rounded-md transition shadow-sm">
                                Undang ke List
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
