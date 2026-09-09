@php
    $tasks = isset($tasks) ? $tasks : (isset($list) ? \App\Models\Task::where('list_id', $list->id)->get() : collect());
    $totalTasks = $tasks->count();
    $completedTasks = $tasks->where('is_done', true)->count();
    $percentage = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

    // Filter langsung dari koleksi $tasks sesuai instruksi README
    $filteredTasks = $tasks;
    $selectedStatus = request('task_status', 'all');
    $selectedPriority = request('task_priority', 'all');

    if ($selectedStatus === 'completed') {
        $filteredTasks = $filteredTasks->where('is_done', true);
    } elseif ($selectedStatus === 'pending') {
        $filteredTasks = $filteredTasks->where('is_done', false);
    }

    if (in_array($selectedPriority, ['low', 'medium', 'high'])) {
        $filteredTasks = $filteredTasks->where('priority', $selectedPriority);
    }
@endphp

<div class="card mb-4 shadow-sm">
    <div class="card-header bg-white py-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="mb-0 fw-bold">Daftar Tugas</h5>
            <a href="{{ route('tasks.create', $list) }}" class="btn btn-sm btn-primary">
                + Tambah Tugas
            </a>
        </div>

        <!-- Progress Bar Penyelesaian -->
        <div class="mt-3">
            <div class="d-flex justify-content-between align-items-center small text-muted mb-1">
                <span>Progres: <strong>{{ $completedTasks }}</strong> dari <strong>{{ $totalTasks }}</strong> selesai</span>
                <span class="fw-bold text-success">{{ $percentage }}%</span>
            </div>
            <div class="progress" style="height: 8px;">
                <div class="progress-bar bg-success" 
                     role="progressbar" 
                     style="width: {{ $percentage }}%" 
                     aria-valuenow="{{ $percentage }}" 
                     aria-valuemin="0" 
                     aria-valuemax="100">
                </div>
            </div>
        </div>

        <!-- Filter Task (Status & Prioritas) -->
        <form method="GET" action="{{ route('lists.show', $list) }}" class="row g-2 mt-3 align-items-center">
            <div class="col-auto">
                <span class="small fw-semibold text-secondary">Filter:</span>
            </div>
            <div class="col-auto">
                <select name="task_status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="all" {{ $selectedStatus === 'all' ? 'selected' : '' }}>Semua Status</option>
                    <option value="pending" {{ $selectedStatus === 'pending' ? 'selected' : '' }}>Belum Selesai</option>
                    <option value="completed" {{ $selectedStatus === 'completed' ? 'selected' : '' }}>Sudah Selesai</option>
                </select>
            </div>
            <div class="col-auto">
                <select name="task_priority" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="all" {{ $selectedPriority === 'all' ? 'selected' : '' }}>Semua Prioritas</option>
                    <option value="high" {{ $selectedPriority === 'high' ? 'selected' : '' }}>Tinggi (High)</option>
                    <option value="medium" {{ $selectedPriority === 'medium' ? 'selected' : '' }}>Sedang (Medium)</option>
                    <option value="low" {{ $selectedPriority === 'low' ? 'selected' : '' }}>Rendah (Low)</option>
                </select>
            </div>
            @if($selectedStatus !== 'all' || $selectedPriority !== 'all')
                <div class="col-auto">
                    <a href="{{ route('lists.show', $list) }}" class="btn btn-sm btn-link text-decoration-none text-danger p-0">
                        Reset Filter
                    </a>
                </div>
            @endif
        </form>
    </div>

    <div class="card-body">
        @if($filteredTasks->isEmpty())
            <div class="alert alert-light text-center text-muted mb-0 border py-4">
                @if($totalTasks === 0)
                    Belum ada tugas di daftar ini. Klik <strong>+ Tambah Tugas</strong> untuk menambahkan tugas baru.
                @else
                    Tidak ada tugas yang sesuai dengan filter yang dipilih.
                @endif
            </div>
        @else
            <div class="list-group list-group-flush">
                @foreach($filteredTasks as $task)
                    @php
                        $isOverdue = false;
                        if (!$task->is_done && $task->due_date) {
                            $isOverdue = \Carbon\Carbon::parse($task->due_date)->endOfDay()->isPast();
                        }
                    @endphp
                    <div class="list-group-item d-flex justify-content-between align-items-start px-0 py-3 {{ $task->is_done ? 'bg-light text-muted' : '' }}">
                        <div class="d-flex align-items-start me-3">
                            <!-- Toggle Button Status -->
                            <form action="{{ route('tasks.toggle', $task) }}" method="POST" class="me-3 mt-1">
                                @csrf
                                @method('PATCH')
                                <button type="submit" 
                                        class="btn btn-sm p-0 border-0 bg-transparent" 
                                        title="{{ $task->is_done ? 'Tandai belum selesai' : 'Tandai sudah selesai' }}">
                                    @if($task->is_done)
                                        <span class="badge bg-success rounded-pill px-2 py-1">✓ Selesai</span>
                                    @else
                                        <span class="badge bg-secondary rounded-pill px-2 py-1">○ Belum</span>
                                    @endif
                                </button>
                            </form>

                            <div>
                                <h6 class="mb-1 fw-bold {{ $task->is_done ? 'text-decoration-line-through text-muted' : 'text-dark' }}">
                                    {{ $task->title }}
                                </h6>
                                @if($task->description)
                                    <p class="mb-1 small text-secondary">
                                        {{ $task->description }}
                                    </p>
                                @endif

                                <!-- Badge Prioritas & Penanda Tenggat Waktu -->
                                <div class="d-flex flex-wrap gap-2 align-items-center mt-2 small">
                                    <!-- Badge Prioritas -->
                                    @if($task->priority === 'high')
                                        <span class="badge bg-danger">Prioritas Tinggi</span>
                                    @elseif($task->priority === 'medium')
                                        <span class="badge bg-warning text-dark">Prioritas Sedang</span>
                                    @else
                                        <span class="badge bg-info text-dark">Prioritas Rendah</span>
                                    @endif

                                    <!-- Penanda Tenggat Waktu -->
                                    @if($task->due_date)
                                        @if($isOverdue)
                                            <span class="badge bg-danger text-white">
                                                ⚠️ Terlewat: {{ \Carbon\Carbon::parse($task->due_date)->format('d M Y') }}
                                            </span>
                                        @else
                                            <span class="text-muted">
                                                📅 Tenggat: {{ \Carbon\Carbon::parse($task->due_date)->format('d M Y') }}
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Aksi Edit & Hapus -->
                        <div class="d-flex align-items-center gap-1">
                            <a href="{{ route('tasks.edit', $task) }}" class="btn btn-sm btn-outline-secondary" title="Edit Tugas">
                                Edit
                            </a>
                            <form action="{{ route('tasks.destroy', $task) }}" 
                                  method="POST" 
                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus tugas ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Tugas">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
