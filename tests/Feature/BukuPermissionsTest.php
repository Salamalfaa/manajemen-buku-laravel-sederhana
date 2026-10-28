<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BukuPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_guests_are_redirected_to_login_from_the_dashboard(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_each_role_can_view_the_dashboard_and_book_catalog(): void
    {
        foreach (['Admin', 'Dosen', 'Mahasiswa'] as $role) {
            $user = User::role($role)->firstOrFail();

            $this->actingAs($user)
                ->get(route('dashboard'))
                ->assertOk();

            $this->get(route('buku.index'))
                ->assertOk();
        }
    }

    public function test_dashboard_analytics_reflect_current_book_and_role_data(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        Buku::create([
            'judul' => 'Buku Analitik Terbaru',
            'penulis' => 'Penulis Baru',
            'penerbit' => 'Penerbit Baru',
            'tahun_terbit' => 2025,
        ]);

        $this->actingAs(User::role('Admin')->firstOrFail())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Buku menurut tahun terbit')
            ->assertSee('Akun berdasarkan role')
            ->assertSee('50,0%', false)
            ->assertSee('40,0%', false);
    }

    public function test_book_catalog_search_and_year_filters_show_matching_results(): void
    {
        $this->actingAs(User::role('Mahasiswa')->firstOrFail())
            ->get(route('buku.index', ['search' => 'Senja']))
            ->assertOk()
            ->assertSee('Senja di Ujung Kota')
            ->assertDontSee('Arsitektur Pikiran');

        $this->get(route('buku.index', ['year' => 2025]))
            ->assertOk()
            ->assertSee('Langit yang Kita Bagi')
            ->assertDontSee('Senja di Ujung Kota');
    }

    public function test_demo_accounts_use_the_configured_demo_password(): void
    {
        foreach (['Admin', 'Dosen', 'Mahasiswa'] as $role) {
            $user = User::role($role)->firstOrFail();

            $this->assertTrue(Hash::check(config('app.demo_user_password'), $user->password));
        }
    }

    public function test_authenticated_activity_updates_the_last_seen_timestamp(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk();

        $this->assertNotNull($user->fresh()->last_seen_at);
    }

    public function test_admin_can_see_account_status_and_formatted_last_active_time(): void
    {
        $admin = User::role('Admin')->firstOrFail();
        $dosen = User::role('Dosen')->firstOrFail();
        DB::table('sessions')->insert([
            'id' => 'active-admin-session',
            'user_id' => $admin->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Feature test',
            'payload' => '',
            'last_activity' => now()->timestamp,
        ]);
        $dosen->forceFill([
            'last_seen_at' => Carbon::parse('2026-01-15 14:32:45', config('app.timezone')),
        ])->save();

        $response = $this->actingAs($admin)->get(route('users.roles.index'));

        $response
            ->assertOk()
            ->assertSee('Online')
            ->assertSee('Offline')
            ->assertSee('Belum pernah aktif')
            ->assertSee('14:32:45')
            ->assertSee('Kamis, 15 Januari 2026');
    }

    public function test_dosen_can_create_and_update_but_cannot_delete_books(): void
    {
        $this->actingAs(User::role('Dosen')->firstOrFail());

        $this->post(route('buku.store'), [
            'judul' => 'Buku Dosen',
            'penulis' => 'Penulis',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 2025,
        ])->assertRedirect(route('buku.index'));

        $buku = Buku::where('judul', 'Buku Dosen')->firstOrFail();

        $this->put(route('buku.update', $buku), [
            'judul' => 'Buku Dosen Revisi',
            'penulis' => 'Penulis',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 2025,
        ])->assertRedirect(route('buku.index'));

        $this->assertDatabaseHas('bukus', ['id' => $buku->id, 'judul' => 'Buku Dosen Revisi']);
        $this->delete(route('buku.destroy', $buku))->assertForbidden();
    }

    public function test_mahasiswa_cannot_mutate_books_even_by_direct_request(): void
    {
        $buku = Buku::firstOrFail();
        $this->actingAs(User::role('Mahasiswa')->firstOrFail());

        $this->get(route('buku.create'))->assertForbidden();
        $this->post(route('buku.store'), [
            'judul' => 'Tidak boleh',
            'penulis' => 'Penulis',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 2025,
        ])->assertForbidden();
        $this->get(route('buku.edit', $buku))->assertForbidden();
        $this->put(route('buku.update', $buku), [
            'judul' => 'Tidak boleh',
            'penulis' => 'Penulis',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 2025,
        ])->assertForbidden();
        $this->delete(route('buku.destroy', $buku))->assertForbidden();
    }

    public function test_only_admin_can_delete_books_and_manage_roles(): void
    {
        $buku = Buku::firstOrFail();
        $this->actingAs(User::role('Admin')->firstOrFail());

        $this->get(route('users.roles.index'))->assertOk();
        $this->post(route('buku.store'), [
            'judul' => 'Buku Admin',
            'penulis' => 'Penulis',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 2025,
        ])->assertRedirect(route('buku.index'));

        $bukuAdmin = Buku::where('judul', 'Buku Admin')->firstOrFail();
        $this->put(route('buku.update', $bukuAdmin), [
            'judul' => 'Buku Admin Revisi',
            'penulis' => 'Penulis',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 2025,
        ])->assertRedirect(route('buku.index'));
        $this->assertDatabaseHas('bukus', ['id' => $bukuAdmin->id, 'judul' => 'Buku Admin Revisi']);

        $this->delete(route('buku.destroy', $buku))->assertRedirect(route('buku.index'));
        $this->assertDatabaseMissing('bukus', ['id' => $buku->id]);
    }

    public function test_non_admin_roles_cannot_manage_user_roles(): void
    {
        $admin = User::role('Admin')->firstOrFail();

        foreach (['Dosen', 'Mahasiswa'] as $role) {
            $user = User::role($role)->firstOrFail();
            $this->actingAs($user)->get(route('users.roles.index'))->assertForbidden();
            $this->patch(route('users.roles.update', $admin), ['role' => 'Mahasiswa'])->assertForbidden();
        }
    }

    public function test_admin_can_change_roles_but_cannot_demote_themselves(): void
    {
        $admin = User::role('Admin')->firstOrFail();
        $dosen = User::role('Dosen')->firstOrFail();
        $this->actingAs($admin);

        $this->patch(route('users.roles.update', $dosen), ['role' => 'Mahasiswa'])
            ->assertRedirect();
        $this->assertTrue($dosen->fresh()->hasRole('Mahasiswa'));

        $this->patch(route('users.roles.update', $admin), ['role' => 'Dosen'])
            ->assertSessionHasErrors('role');
        $this->assertTrue($admin->fresh()->hasRole('Admin'));
    }

    public function test_new_registrations_are_assigned_the_read_only_role(): void
    {
        $this->post(route('register'), [
            'name' => 'Akun Baru',
            'email' => 'baru@example.com',
            'password' => 'Password-12345',
            'password_confirmation' => 'Password-12345',
            'role' => 'Admin',
        ])->assertRedirect(route('dashboard'));

        $user = User::where('email', 'baru@example.com')->firstOrFail();
        $this->assertTrue($user->hasRole('Mahasiswa'));
        $this->assertFalse($user->hasRole('Admin'));
    }
}
