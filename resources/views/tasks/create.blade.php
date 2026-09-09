@extends('layouts.app')

@section('title', 'Tambah Tugas Baru')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Tambah Tugas Baru</h1>
        <a href="{{ route('lists.show', $list) }}" class="text-sm text-indigo-600 hover:underline">&larr; Kembali ke Detail List</a>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
        <form action="{{ route('tasks.store', $list) }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label for="title" class="block text-sm font-semibold text-gray-700">Judul Tugas <span class="text-red-500">*</span></label>
                <input type="text" id="title" name="title" value="{{ old('title') }}" required
                    placeholder="Contoh: Menyelesaikan bab 1 laporan"
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>

            <div>
                <label for="description" class="block text-sm font-semibold text-gray-700">Deskripsi</label>
                <textarea id="description" name="description" rows="3"
                    placeholder="Rincian atau catatan tugas (opsional)"
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="priority" class="block text-sm font-semibold text-gray-700">Prioritas <span class="text-red-500">*</span></label>
                    <select id="priority" name="priority" required
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <option value="low" @selected(old('priority') === 'low')>Rendah (Low)</option>
                        <option value="medium" @selected(old('priority', 'medium') === 'medium')>Sedang (Medium)</option>
                        <option value="high" @selected(old('priority') === 'high')>Tinggi (High)</option>
                    </select>
                </div>

                <div>
                    <label for="due_date" class="block text-sm font-semibold text-gray-700">Tenggat Waktu (Deadline)</label>
                    <input type="date" id="due_date" name="due_date" value="{{ old('due_date') }}"
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex items-center justify-end space-x-3">
                <a href="{{ route('lists.show', $list) }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-md transition">Batal</a>
                <button type="submit" class="px-5 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-md shadow transition">Simpan Tugas</button>
            </div>
        </form>
    </div>
</div>
@endsection
