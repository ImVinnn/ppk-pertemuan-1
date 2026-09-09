<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Tamu (belum login) diarahkan ke halaman login.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/lists')->assertRedirect('/login');
    }

    /**
     * User yang sudah login dapat membuka daftar list.
     */
    public function test_authenticated_user_can_view_lists(): void
    {
        $user = \App\Models\User::factory()->create();

        $this->actingAs($user)->get('/lists')->assertStatus(200);
    }
}
