@extends('layouts.app')

@section('title', 'Buat List Baru')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Buat List Tugas Baru</h1>
        <a href="{{ route('lists.index') }}" class="text-sm text-indigo-600 hover:underline">
            &larr; Kembali ke Daftar List
        </a>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
        <form action="{{ route('lists.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label for="title" class="block text-sm font-semibold text-gray-700">
                    Nama List / Proyek <span class="text-red-500">*</span>
                </label>
                <input type="text" name="title" id="title" required
                    value="{{ old('title') }}"
                    placeholder="Contoh: Sprint Minggu 1, Tugas Akhir, dll."
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>

            <div>
                <label for="description" class="block text-sm font-semibold text-gray-700">
                    Deskripsi Singkat (Opsional)
                </label>
                <textarea name="description" id="description" rows="4"
                    placeholder="Tuliskan tujuan atau keterangan dari daftar tugas ini..."
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">{{ old('description') }}</textarea>
            </div>

            <div class="pt-4 border-t border-gray-100 flex items-center justify-end space-x-3">
                <a href="{{ route('lists.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-md transition">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-md shadow transition">
                    Simpan List
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
