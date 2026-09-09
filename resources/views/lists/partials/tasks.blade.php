@php
    $tasks = $tasks ?? ($list->tasks ?? collect());
    $totalTasks = $tasks->count();
    $completedTasks = $tasks->where('is_done', true)->count();
    $percentage = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

    $selectedStatus = request('task_status', 'all');
    $selectedPriority = request('task_priority', 'all');

    $filteredTasks = $tasks;
    if ($selectedStatus === 'completed') {
        $filteredTasks = $filteredTasks->where('is_done', true);
    } elseif ($selectedStatus === 'pending') {
        $filteredTasks = $filteredTasks->where('is_done', false);
    }
    if (in_array($selectedPriority, ['low', 'medium', 'high'])) {
        $filteredTasks = $filteredTasks->where('priority', $selectedPriority);
    }

    $selectClass = 'text-xs rounded-md border border-gray-300 px-2 py-1.5 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500';
@endphp

<div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 space-y-4">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-bold text-gray-800">Daftar Tugas</h2>
        <a href="{{ route('tasks.create', $list) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold px-3 py-1.5 rounded-md transition shadow-sm">
            + Tambah Tugas
        </a>
    </div>

    <!-- Progres penyelesaian -->
    <div class="space-y-1">
        <div class="flex items-center justify-between text-xs font-semibold text-gray-600">
            <span>Progres: <strong>{{ $completedTasks }}</strong> dari <strong>{{ $totalTasks }}</strong> selesai</span>
            <span class="text-indigo-600">{{ $percentage }}%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
            <div class="bg-indigo-600 h-2.5 rounded-full" style="width: {{ $percentage }}%"></div>
        </div>
    </div>

    <!-- Filter -->
    <form method="GET" action="{{ route('lists.show', $list) }}" class="flex flex-wrap items-center gap-2 pt-2 border-t border-gray-100">
        <span class="text-xs font-semibold text-gray-500">Filter:</span>
        <select name="task_status" class="{{ $selectClass }}" onchange="this.form.submit()">
            <option value="all" @selected($selectedStatus === 'all')>Semua Status</option>
            <option value="pending" @selected($selectedStatus === 'pending')>Belum Selesai</option>
            <option value="completed" @selected($selectedStatus === 'completed')>Sudah Selesai</option>
        </select>
        <select name="task_priority" class="{{ $selectClass }}" onchange="this.form.submit()">
            <option value="all" @selected($selectedPriority === 'all')>Semua Prioritas</option>
            <option value="high" @selected($selectedPriority === 'high')>Tinggi (High)</option>
            <option value="medium" @selected($selectedPriority === 'medium')>Sedang (Medium)</option>
            <option value="low" @selected($selectedPriority === 'low')>Rendah (Low)</option>
        </select>
        @if($selectedStatus !== 'all' || $selectedPriority !== 'all')
            <a href="{{ route('lists.show', $list) }}" class="text-xs font-semibold text-red-500 hover:underline">Reset Filter</a>
        @endif
    </form>

    <!-- Daftar -->
    @if($filteredTasks->isEmpty())
        <div class="border border-dashed border-gray-200 rounded-lg p-8 text-center text-gray-400 text-sm">
            @if($totalTasks === 0)
                📌 Belum ada tugas di daftar ini. Klik <strong>+ Tambah Tugas</strong> untuk menambahkan.
            @else
                Tidak ada tugas yang sesuai dengan filter yang dipilih.
            @endif
        </div>
    @else
        <div class="divide-y divide-gray-100">
            @foreach($filteredTasks as $task)
                @php
                    $isOverdue = ! $task->is_done && $task->due_date
                        && \Carbon\Carbon::parse($task->due_date)->endOfDay()->isPast();
                @endphp
                <div class="flex items-start justify-between gap-3 py-3 {{ $task->is_done ? 'opacity-60' : '' }}">
                    <div class="flex items-start gap-3">
                        <form action="{{ route('tasks.toggle', $task) }}" method="POST" class="mt-0.5">
                            @csrf
                            @method('PATCH')
                            <button type="submit" title="{{ $task->is_done ? 'Tandai belum selesai' : 'Tandai selesai' }}"
                                class="text-[11px] font-bold px-2 py-1 rounded-full {{ $task->is_done ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $task->is_done ? '✓ Selesai' : '○ Belum' }}
                            </button>
                        </form>

                        <div>
                            <h3 class="font-bold text-sm {{ $task->is_done ? 'line-through text-gray-400' : 'text-gray-900' }}">
                                {{ $task->title }}
                            </h3>
                            @if($task->description)
                                <p class="text-xs text-gray-500 mt-0.5">{{ $task->description }}</p>
                            @endif
                            <div class="flex flex-wrap items-center gap-2 mt-1.5 text-xs">
                                @php
                                    $prio = [
                                        'high' => ['Prioritas Tinggi', 'bg-red-100 text-red-700'],
                                        'medium' => ['Prioritas Sedang', 'bg-amber-100 text-amber-800'],
                                        'low' => ['Prioritas Rendah', 'bg-sky-100 text-sky-700'],
                                    ][$task->priority] ?? ['Prioritas', 'bg-gray-100 text-gray-700'];
                                @endphp
                                <span class="font-semibold px-2 py-0.5 rounded {{ $prio[1] }}">{{ $prio[0] }}</span>
                                @if($task->due_date)
                                    <span class="{{ $isOverdue ? 'font-semibold text-red-600' : 'text-gray-400' }}">
                                        {{ $isOverdue ? '⚠️ Terlewat: ' : '📅 Tenggat: ' }}{{ \Carbon\Carbon::parse($task->due_date)->format('d M Y') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5 flex-shrink-0">
                        <a href="{{ route('tasks.edit', $task) }}" class="text-xs font-semibold border border-gray-300 hover:bg-gray-50 text-gray-700 px-2.5 py-1 rounded-md transition">Edit</a>
                        <form action="{{ route('tasks.destroy', $task) }}" method="POST"
                            onsubmit="return confirm('Yakin ingin menghapus tugas ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-semibold border border-red-300 text-red-600 hover:bg-red-50 px-2.5 py-1 rounded-md transition">Hapus</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
