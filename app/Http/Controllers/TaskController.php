<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TodoList;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Pastikan user yang login adalah owner atau anggota list terkait.
     */
    private function authorizeList(Request $request, TodoList $list): void
    {
        abort_unless($list->hasAccess($request->user()), 403, 'Anda tidak memiliki akses ke list ini.');
    }

    /**
     * Menampilkan form tambah task untuk sebuah list.
     */
    public function create(Request $request, TodoList $list)
    {
        $this->authorizeList($request, $list);

        return view('tasks.create', [
            'list' => $list,
        ]);
    }

    /**
     * Menyimpan task baru ke dalam list.
     */
    public function store(Request $request, TodoList $list)
    {
        $this->authorizeList($request, $list);

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority'    => 'required|in:low,medium,high',
            'due_date'    => 'nullable|date',
        ]);

        $list->tasks()->create([
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'priority'    => $validated['priority'],
            'due_date'    => $validated['due_date'] ?? null,
            'is_done'     => false,
        ]);

        return redirect()->route('lists.show', $list)->with('success', 'Tugas berhasil ditambahkan.');
    }

    /**
     * Menampilkan form edit task.
     */
    public function edit(Request $request, Task $task)
    {
        $this->authorizeList($request, $task->list);

        return view('tasks.edit', [
            'task' => $task,
        ]);
    }

    /**
     * Mengupdate data task.
     */
    public function update(Request $request, Task $task)
    {
        $this->authorizeList($request, $task->list);

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
    public function destroy(Request $request, Task $task)
    {
        $this->authorizeList($request, $task->list);

        $listId = $task->list_id;
        $task->delete();

        return redirect()->route('lists.show', $listId)->with('success', 'Tugas berhasil dihapus.');
    }

    /**
     * Toggle status selesai (is_done) sebuah task.
     */
    public function toggle(Request $request, Task $task)
    {
        $this->authorizeList($request, $task->list);

        $task->update([
            'is_done' => ! $task->is_done,
        ]);

        return back()->with('success', 'Status tugas berhasil diperbarui.');
    }
}
