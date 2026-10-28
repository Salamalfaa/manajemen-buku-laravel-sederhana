<?php

namespace Tests\Feature;

use App\Models\PasswordResetRequest;
use App\Models\User;
use App\Notifications\PasswordResetRequestRejected;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admin_can_view_account_creation_and_password_request_management(): void
    {
        $request = PasswordResetRequest::create([
            'user_id' => User::role('Mahasiswa')->firstOrFail()->id,
            'email' => User::role('Mahasiswa')->firstOrFail()->email,
        ]);

        $this->actingAs(User::role('Admin')->firstOrFail())
            ->get(route('users.management.index'))
            ->assertOk()
            ->assertSee('Buat akun pengguna')
            ->assertSeeInOrder([
                'Nomor telepon',
                'Password baru',
                'Konfirmasi password baru',
                'Role akun',
                'Buat akun',
            ])
            ->assertDontSee('Buat akun &amp; kirim tautan')
            ->assertDontSee('Password awal tidak dikirim sebagai teks.')
            ->assertSee('Permintaan reset password')
            ->assertSee('Setujui')
            ->assertSee('Tolak')
            ->assertSee('Hapus riwayat permintaan reset')
            ->assertSee('Hapus riwayat permintaan?')
            ->assertSee('Ya, hapus riwayat')
            ->assertSee('Tidak, kembali')
            ->assertSee($request->email);
    }

    public function test_admin_sees_an_iphone_style_account_status_switch_for_other_users(): void
    {
        $this->actingAs(User::role('Admin')->firstOrFail())
            ->get(route('users.roles.index'))
            ->assertOk()
            ->assertSee('Status akun')
            ->assertSee('role="switch"', false)
            ->assertSee('Aktif')
            ->assertSee('Nonaktifkan akun Dosen Pengajar');
    }

    public function test_admin_can_open_a_large_profile_photo_preview_from_the_user_list(): void
    {
        $user = User::factory()->create([
            'name' => 'Pengguna Berfoto',
            'email' => 'foto@example.com',
            'avatar_path' => 'avatars/pengguna-berfoto.png',
        ]);

        $this->actingAs(User::role('Admin')->firstOrFail())
            ->get(route('users.roles.index'))
            ->assertOk()
            ->assertSee('data-user-avatar-preview', false)
            ->assertSee('data-avatar-url="http://localhost/storage/avatars/pengguna-berfoto.png"', false)
            ->assertSee('data-avatar-name="Pengguna Berfoto"', false)
            ->assertSee('data-avatar-email="foto@example.com"', false)
            ->assertSee('userAvatarPreviewDialog', false)
            ->assertSee('data-preview-avatar-image', false)
            ->assertSee('Tutup pratinjau foto profil');
    }

    public function test_admin_can_create_an_account_with_a_confirmed_password_and_selected_role(): void
    {
        Notification::fake();
        $admin = User::role('Admin')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('users.management.store'), [
                'name' => 'Dosen Baru',
                'email' => 'dosen.baru@example.com',
                'phone' => '08123456789',
                'password' => 'StrongPassword123!',
                'password_confirmation' => 'StrongPassword123!',
                'role' => 'Dosen',
            ])
            ->assertRedirect(route('users.management.index'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Akun berhasil dibuat dengan password baru dan role yang dipilih.');

        $user = User::where('email', 'dosen.baru@example.com')->firstOrFail();
        $this->assertTrue($user->hasRole('Dosen'));
        $this->assertSame('08123456789', $user->phone);
        $this->assertTrue(Hash::check('StrongPassword123!', $user->password));
        Notification::assertNothingSent();
    }

    public function test_admin_cannot_create_an_account_when_password_confirmation_does_not_match(): void
    {
        $this->actingAs(User::role('Admin')->firstOrFail())
            ->from(route('users.management.index'))
            ->post(route('users.management.store'), [
                'name' => 'Dosen Invalid',
                'email' => 'dosen.invalid@example.com',
                'phone' => '08123456789',
                'password' => 'StrongPassword123!',
                'password_confirmation' => 'DifferentPassword123!',
                'role' => 'Dosen',
            ])
            ->assertRedirect(route('users.management.index'))
            ->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['email' => 'dosen.invalid@example.com']);
    }

    public function test_admin_can_approve_a_password_reset_request_and_send_the_reset_link(): void
    {
        Notification::fake();
        $user = User::role('Mahasiswa')->firstOrFail();
        $request = PasswordResetRequest::create([
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        $this->actingAs(User::role('Admin')->firstOrFail())
            ->post(route('users.password-reset-requests.approve', $request))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('password_reset_requests', [
            'id' => $request->id,
            'status' => PasswordResetRequest::STATUS_APPROVED,
        ]);
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_admin_can_reject_a_password_reset_request_and_notify_the_requester(): void
    {
        Notification::fake();
        $user = User::role('Dosen')->firstOrFail();
        $request = PasswordResetRequest::create([
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        $this->actingAs(User::role('Admin')->firstOrFail())
            ->post(route('users.password-reset-requests.reject', $request))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('password_reset_requests', [
            'id' => $request->id,
            'status' => PasswordResetRequest::STATUS_REJECTED,
        ]);
        Notification::assertSentTo($user, PasswordResetRequestRejected::class);
    }

    public function test_admin_can_delete_a_reset_request_history_without_deleting_the_user_account(): void
    {
        $user = User::role('Mahasiswa')->firstOrFail();
        $request = PasswordResetRequest::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'status' => PasswordResetRequest::STATUS_APPROVED,
            'reviewed_by' => User::role('Admin')->firstOrFail()->id,
            'reviewed_at' => now(),
        ]);

        $this->actingAs(User::role('Admin')->firstOrFail())
            ->delete(route('users.password-reset-requests.destroy', $request))
            ->assertRedirect()
            ->assertSessionHas('success', 'Riwayat permintaan reset password berhasil dihapus. Akun pengguna tidak dihapus.');

        $this->assertDatabaseMissing('password_reset_requests', ['id' => $request->id]);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'email' => $user->email]);
    }

    public function test_non_admin_cannot_open_or_mutate_account_management(): void
    {
        $admin = User::role('Admin')->firstOrFail();
        $user = User::role('Mahasiswa')->firstOrFail();
        $request = PasswordResetRequest::create([
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        $this->actingAs(User::role('Dosen')->firstOrFail())
            ->get(route('users.management.index'))
            ->assertForbidden();

        $this->post(route('users.management.store'), [
            'name' => 'Akun ilegal',
            'email' => 'ilegal@example.com',
            'role' => 'Admin',
        ])->assertForbidden();

        $this->post(route('users.password-reset-requests.approve', $request))
            ->assertForbidden();
        $this->post(route('users.password-reset-requests.reject', $request))
            ->assertForbidden();
        $this->delete(route('users.password-reset-requests.destroy', $request))
            ->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'ilegal@example.com']);
        $this->assertDatabaseHas('password_reset_requests', ['id' => $request->id]);
        $this->assertTrue($admin->fresh()->hasRole('Admin'));
    }

    public function test_reviewed_password_reset_requests_cannot_be_approved_or_rejected_again(): void
    {
        $request = PasswordResetRequest::create([
            'user_id' => User::role('Mahasiswa')->firstOrFail()->id,
            'email' => User::role('Mahasiswa')->firstOrFail()->email,
            'status' => PasswordResetRequest::STATUS_REJECTED,
        ]);

        $this->actingAs(User::role('Admin')->firstOrFail())
            ->post(route('users.password-reset-requests.approve', $request))
            ->assertSessionHasErrors('password_reset_request');

        $this->assertDatabaseHas('password_reset_requests', [
            'id' => $request->id,
            'status' => PasswordResetRequest::STATUS_REJECTED,
        ]);
    }

    public function test_admin_can_toggle_another_accounts_active_status(): void
    {
        $admin = User::role('Admin')->firstOrFail();
        $dosen = User::role('Dosen')->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('users.status.toggle', $dosen))
            ->assertRedirect()
            ->assertSessionHas('account_status', [
                'title' => 'Status akun diperbarui',
                'message' => "Akun {$dosen->name} berhasil dinonaktifkan.",
                'active' => false,
            ]);

        $this->assertFalse($dosen->fresh()->is_active);

        $this->patch(route('users.status.toggle', $dosen))
            ->assertSessionHas('account_status.active', true);

        $this->assertTrue($dosen->fresh()->is_active);
    }

    public function test_json_toggle_returns_the_new_state_and_revokes_the_users_database_sessions(): void
    {
        $admin = User::role('Admin')->firstOrFail();
        $dosen = User::role('Dosen')->firstOrFail();
        DB::table('sessions')->insert([
            'id' => 'deactivated-dosen-session',
            'user_id' => $dosen->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Feature test',
            'payload' => '',
            'last_activity' => now()->timestamp,
        ]);

        $this->actingAs($admin)
            ->patchJson(route('users.status.toggle', $dosen))
            ->assertOk()
            ->assertJson([
                'active' => false,
                'message' => "Akun {$dosen->name} berhasil dinonaktifkan.",
            ]);

        $this->assertDatabaseMissing('sessions', ['id' => 'deactivated-dosen-session']);
    }

    public function test_admin_cannot_deactivate_themselves_or_the_last_active_administrator(): void
    {
        $admin = User::role('Admin')->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('users.status.toggle', $admin))
            ->assertSessionHasErrors('account_status');
        $this->assertTrue($admin->fresh()->is_active);

        $otherAdmin = User::factory()->create(['is_active' => true]);
        $otherAdmin->assignRole('Admin');

        $this->patch(route('users.status.toggle', $otherAdmin))
            ->assertSessionHas('account_status.active', false);
        $this->assertFalse($otherAdmin->fresh()->is_active);

        $this->patch(route('users.status.toggle', $otherAdmin))
            ->assertSessionHas('account_status.active', true);
        $this->assertTrue($otherAdmin->fresh()->is_active);

        $this->assertSame(2, User::role('Admin')->where('is_active', true)->count());
    }

    public function test_only_admin_can_toggle_account_status(): void
    {
        $dosen = User::role('Dosen')->firstOrFail();

        $this->actingAs(User::role('Mahasiswa')->firstOrFail())
            ->patch(route('users.status.toggle', $dosen))
            ->assertForbidden();

        $this->assertTrue($dosen->fresh()->is_active);
    }
}
