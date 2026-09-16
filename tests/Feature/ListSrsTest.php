<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\TodoList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListSrsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * SRS-03: Buat list atomik dan pembuat otomatis tercatat sebagai owner di list_members.
     */
    public function test_create_list_is_atomic_and_creator_is_registered_as_owner_in_list_members(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/lists', [
            'name'        => 'Proyek Utama',
            'description' => 'Deskripsi proyek pengujian',
        ]);
        $list = TodoList::first();
        $this->assertNotNull($list);
        $this->assertEquals('Proyek Utama', $list->name);
        $this->assertEquals($user->id, $list->owner_id);

        // Pastikan terdaftar di pivot list_members dengan role = 'owner'
        $this->assertDatabaseHas('list_user', [
            'list_id' => $list->id,
            'user_id' => $user->id,
            'role'    => 'owner',
        ]);

        $response->assertRedirect(route('lists.show', $list));
    }

    /**
     * SRS-03: Hapus list atomik (hapus tasks, list_members, dan lists terkait).
     */
    public function test_owner_can_delete_list_atomically_cascading_tasks_and_members(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $list = TodoList::create([
            'name'     => 'List Untuk Dihapus',
            'owner_id' => $owner->id,
            'user_id'  => $owner->id,
            'title'    => 'List Untuk Dihapus',
        ]);

        $list->members()->attach($owner->id, ['role' => 'owner']);
        $list->members()->attach($member->id, ['role' => 'member']);

        $task = Task::create([
            'list_id'  => $list->id,
            'title'    => 'Task Uji Coba',
            'priority' => 'medium',
        ]);

        $response = $this->actingAs($owner)->delete('/lists/' . $list->id);

        $response->assertRedirect(route('lists.index'));

        // Pastikan seluruh data bersih dari database
        $this->assertDatabaseMissing('todo_lists', ['id' => $list->id]);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
        $this->assertDatabaseMissing('list_user', ['list_id' => $list->id]);
    }

    /**
     * SRS-03: Otorisasi owner saat menghapus list (non-owner ditolak 403).
     */
    public function test_non_owner_cannot_delete_list(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();

        $list = TodoList::create([
            'name'     => 'List Privat',
            'owner_id' => $owner->id,
            'user_id'  => $owner->id,
            'title'    => 'List Privat',
        ]);

        $response = $this->actingAs($stranger)->delete('/lists/' . $list->id);

        $response->assertStatus(403);
        $this->assertDatabaseHas('todo_lists', ['id' => $list->id]);
    }

    /**
     * SRS-04: Owner dapat menambah anggota ke list (via dropdown user_id atau email).
     */
    public function test_owner_can_add_member_to_list(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $list = TodoList::create([
            'name'     => 'List Kolaborasi',
            'owner_id' => $owner->id,
            'user_id'  => $owner->id,
            'title'    => 'List Kolaborasi',
        ]);
        $list->members()->attach($owner->id, ['role' => 'owner']);

        $response = $this->actingAs($owner)->post("/lists/{$list->id}/members", [
            'user_id' => $member->id,
        ]);

        $response->assertRedirect(route('lists.show', $list));

        $this->assertDatabaseHas('list_user', [
            'list_id' => $list->id,
            'user_id' => $member->id,
            'role'    => 'member',
        ]);
    }

    /**
     * SRS-04: Non-owner ditolak (403) saat mencoba menambah anggota ke list.
     */
    public function test_non_owner_cannot_add_member_to_list(): void
    {
        $owner = User::factory()->create();
        $nonOwner = User::factory()->create();
        $targetUser = User::factory()->create();

        $list = TodoList::create([
            'name'     => 'List Terlindungi',
            'owner_id' => $owner->id,
            'user_id'  => $owner->id,
            'title'    => 'List Terlindungi',
        ]);
        $list->members()->attach($owner->id, ['role' => 'owner']);

        $response = $this->actingAs($nonOwner)->post("/lists/{$list->id}/members", [
            'user_id' => $targetUser->id,
        ]);

        $response->assertStatus(403);
    }

    /**
     * SRS-04: Tidak dapat menambahkan owner sendiri atau anggota duplikat.
     */
    public function test_cannot_add_owner_or_duplicate_member(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $list = TodoList::create([
            'name'     => 'List Uji Duplikasi',
            'owner_id' => $owner->id,
            'user_id'  => $owner->id,
            'title'    => 'List Uji Duplikasi',
        ]);
        $list->members()->attach($owner->id, ['role' => 'owner']);
        $list->members()->attach($member->id, ['role' => 'member']);

        // Coba undang owner sendiri
        $response = $this->actingAs($owner)->post("/lists/{$list->id}/members", [
            'user_id' => $owner->id,
        ]);
        $response->assertSessionHas('error');

        // Coba undang member yang sudah terdaftar
        $response = $this->actingAs($owner)->post("/lists/{$list->id}/members", [
            'user_id' => $member->id,
        ]);
        $response->assertSessionHas('error');
    }

    /**
     * SRS-04: Owner dapat mengeluarkan anggota dari list.
     */
    public function test_owner_can_remove_member_from_list(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $list = TodoList::create([
            'name'     => 'List Uji Kick',
            'owner_id' => $owner->id,
            'user_id'  => $owner->id,
            'title'    => 'List Uji Kick',
        ]);
        $list->members()->attach($owner->id, ['role' => 'owner']);
        $list->members()->attach($member->id, ['role' => 'member']);

        $response = $this->actingAs($owner)->delete("/lists/{$list->id}/members/{$member->id}");

        $response->assertRedirect(route('lists.show', $list));
        $this->assertDatabaseMissing('list_user', [
            'list_id' => $list->id,
            'user_id' => $member->id,
        ]);
    }

    /**
     * SRS-04: Non-owner ditolak (403) saat mencoba mengeluarkan anggota.
     */
    public function test_non_owner_cannot_remove_member_from_list(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $stranger = User::factory()->create();

        $list = TodoList::create([
            'name'     => 'List Uji Otorisasi Kick',
            'owner_id' => $owner->id,
            'user_id'  => $owner->id,
            'title'    => 'List Uji Otorisasi Kick',
        ]);
        $list->members()->attach($owner->id, ['role' => 'owner']);
        $list->members()->attach($member->id, ['role' => 'member']);

        $response = $this->actingAs($stranger)->delete("/lists/{$list->id}/members/{$member->id}");

        $response->assertStatus(403);
    }
}
