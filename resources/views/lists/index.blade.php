@extends('layouts.app')

@section('title', 'Daftar List')

@section('content')
<div class="space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Daftar List Tugas</h1>
            <p class="text-sm text-gray-500">Kelola dan pantau seluruh daftar tugas pribadi maupun tim Anda.</p>
        </div>
        <a href="{{ route('lists.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium shadow transition">
            + Buat List Baru
        </a>
    </div>

    <!-- Bagian 1: List Pribadi (Owner) -->
    <div class="space-y-4">
        <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
            <span>👑 List Pribadi (Milik Saya)</span>
            <span class="bg-indigo-100 text-indigo-800 text-xs px-2.5 py-0.5 rounded-full font-bold">
                {{ $ownedLists->count() }}
            </span>
        </h2>

        @if($ownedLists->isEmpty())
            <div class="bg-white rounded-lg border border-dashed border-gray-300 p-8 text-center text-gray-500">
                <p>Belum ada list pribadi yang dibuat.</p>
                <a href="{{ route('lists.create') }}" class="mt-2 inline-block text-indigo-600 hover:underline text-sm font-medium">
                    Buat list pertama Anda sekarang &rarr;
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($ownedLists as $list)
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition p-5 flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="font-bold text-gray-900 text-lg line-clamp-1">{{ $list->title }}</h3>
                                <span class="bg-yellow-100 text-yellow-800 text-xs font-semibold px-2 py-0.5 rounded">Owner</span>
                            </div>
                            <p class="text-sm text-gray-600 mt-2 line-clamp-2">
                                {{ $list->description ?? 'Tidak ada deskripsi.' }}
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                            <span>👥 {{ $list->members->count() }} Anggota</span>
                            <a href="{{ route('lists.show', $list) }}" class="text-indigo-600 font-semibold hover:underline">
                                Lihat Detail &rarr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Bagian 2: List Kolaborasi (Anggota) -->
    <div class="space-y-4 pt-4">
        <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
            <span>🤝 List Kolaborasi Tim (Saya sebagai Anggota)</span>
            <span class="bg-emerald-100 text-emerald-800 text-xs px-2.5 py-0.5 rounded-full font-bold">
                {{ $collaboratedLists->count() }}
            </span>
        </h2>

        @if($collaboratedLists->isEmpty())
            <div class="bg-white rounded-lg border border-dashed border-gray-300 p-6 text-center text-gray-500 text-sm">
                Anda belum diundang ke dalam list kolaborasi mana pun.
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($collaboratedLists as $list)
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition p-5 flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="font-bold text-gray-900 text-lg line-clamp-1">{{ $list->title }}</h3>
                                <span class="bg-emerald-100 text-emerald-800 text-xs font-semibold px-2 py-0.5 rounded">Anggota</span>
                            </div>
                            <p class="text-sm text-gray-600 mt-2 line-clamp-2">
                                {{ $list->description ?? 'Tidak ada deskripsi.' }}
                            </p>
                            <p class="text-xs text-gray-400 mt-2">
                                Dibuat oleh: <span class="font-medium text-gray-600">{{ $list->owner->name ?? 'User' }}</span>
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                            <span>👥 {{ $list->members->count() }} Anggota</span>
                            <a href="{{ route('lists.show', $list) }}" class="text-indigo-600 font-semibold hover:underline">
                                Lihat Detail &rarr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
