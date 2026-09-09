<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Menampilkan form tambah task untuk sebuah list.
     */
    public function create($list)
    {
        return view('tasks.create', [
            'list' => $list,
        ]);
    }

    /**
     * Menyimpan task baru ke dalam list.
     */
    public function store(Request $request, $list)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority'    => 'required|in:low,medium,high',
            'due_date'    => 'nullable|date',
        ]);

        $listId = is_object($list) ? $list->id : $list;

        Task::create([
            'list_id'     => $listId,
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'priority'    => $validated['priority'],
            'due_date'    => $validated['due_date'] ?? null,
            'is_done'     => false,
        ]);

        return redirect()->route('lists.show', $listId)->with('success', 'Tugas berhasil ditambahkan.');
    }

    /**
     * Menampilkan form edit task.
     */
    public function edit(Task $task)
    {
        return view('tasks.edit', [
            'task' => $task,
        ]);
    }

    /**
     * Mengupdate data task.
     */
    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority'    => 'required|in:low,medium,high',
            'due_date'    => 'nullable|date',
        ]);

        $task->update([
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'priority'    => $validated['priority'],
            'due_date'    => $validated['due_date'] ?? null,
        ]);

        return redirect()->route('lists.show', $task->list_id)->with('success', 'Tugas berhasil diperbarui.');
    }

    /**
     * Menghapus task dari database.
     */
    public function destroy(Task $task)
    {
        $listId = $task->list_id;
        $task->delete();

        return redirect()->route('lists.show', $listId)->with('success', 'Tugas berhasil dihapus.');
    }

    /**
     * Toggle status selesai (is_done) sebuah task.
     */
    public function toggle(Task $task)
    {
        $task->update([
            'is_done' => !$task->is_done,
        ]);

        return back()->with('success', 'Status tugas berhasil diperbarui.');
    }
}
