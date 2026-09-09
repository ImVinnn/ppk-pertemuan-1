@extends('layouts.app')

@section('title', 'Edit List: ' . $list->title)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Pengaturan List</span>
            <h1 class="text-2xl font-bold text-gray-900">Edit Informasi List</h1>
        </div>
        <a href="{{ route('lists.show', $list) }}" class="text-sm text-indigo-600 hover:underline">
            &larr; Batal & Kembali ke Detail
        </a>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
        <form action="{{ route('lists.update', $list) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="title" class="block text-sm font-semibold text-gray-700">
                    Nama List / Proyek <span class="text-red-500">*</span>
                </label>
                <input type="text" name="title" id="title" required
                    value="{{ old('title', $list->title) }}"
                    placeholder="Contoh: Sprint Minggu 1, Tugas Akhir, dll."
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>

            <div>
                <label for="description" class="block text-sm font-semibold text-gray-700">
                    Deskripsi Singkat (Opsional)
                </label>
                <textarea name="description" id="description" rows="4"
                    placeholder="Tuliskan tujuan atau keterangan dari daftar tugas ini..."
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">{{ old('description', $list->description) }}</textarea>
            </div>

            <div class="pt-4 border-t border-gray-100 flex items-center justify-end space-x-3">
                <a href="{{ route('lists.show', $list) }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-md transition">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-md shadow transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
