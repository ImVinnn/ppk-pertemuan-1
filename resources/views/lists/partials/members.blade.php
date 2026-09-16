<div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 space-y-4">
    <h2 class="text-base font-bold text-gray-800 flex items-center justify-between">
        <span>👥 Anggota Tim</span>
        <span class="text-xs font-normal text-gray-500">{{ $list->members->where('id', '!=', $list->owner_id)->count() + 1 }} Orang</span>
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

        <!-- Anggota Kolaborator (Kecualikan Owner agar tidak duplikat di view) -->
        @foreach($list->members as $member)
            @if($member->id !== $list->owner_id)
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
            @endif
        @endforeach

        @if($list->members->where('id', '!=', $list->owner_id)->isEmpty())
            <p class="text-xs text-gray-400 text-center py-2">Belum ada anggota tim lain.</p>
        @endif
    </div>

    @if($isOwner)
        <div class="pt-4 border-t border-gray-100">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">➕ Tambah Anggota (SRS-04)</h3>
            <form action="{{ route('lists.members.store', $list) }}" method="POST" class="space-y-3">
                @csrf
                @if(isset($availableUsers) && $availableUsers->isNotEmpty())
                    <div>
                        <label for="user_id" class="block text-xs font-medium text-gray-700 mb-1">Pilih Pengguna dari Dropdown</label>
                        <select name="user_id" id="user_id" class="w-full text-xs rounded-md border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <option value="">-- Pilih Pengguna --</option>
                            @foreach($availableUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <div>
                        <label for="email" class="block text-xs font-medium text-gray-700 mb-1">Email Pengguna</label>
                        <input type="email" name="email" id="email" required placeholder="Masukkan email anggota..."
                            class="w-full text-xs rounded-md border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                @endif
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold py-2 px-3 rounded-md transition shadow-sm">
                    Undang ke List
                </button>
            </form>
        </div>
    @endif
</div>
