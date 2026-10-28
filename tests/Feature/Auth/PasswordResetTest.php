<?php

namespace Tests\Feature\Auth;

use App\Models\PasswordResetRequest as PasswordResetRequestModel;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200)
            ->assertSee('Keamanan akun')
            ->assertSee('Kirim permintaan reset');
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email])
            ->assertSessionHas('status');

        $this->assertDatabaseHas('password_reset_requests', [
            'user_id' => $user->id,
            'email' => $user->email,
            'status' => PasswordResetRequestModel::STATUS_PENDING,
        ]);
        Notification::assertNothingSent();
    }

    public function test_unknown_email_receives_the_same_confirmation_without_a_request(): void
    {
        $this->post('/forgot-password', ['email' => 'unknown@example.com'])
            ->assertSessionHas('status', 'Jika email terdaftar, permintaan reset password akan ditinjau administrator. Jika disetujui, Anda dapat membuat password baru pada halaman ini.');

        $this->assertDatabaseCount('password_reset_requests', 0);

        $this->get(route('password.request.status'))
            ->assertJson([
                'state' => 'checking',
                'message' => 'Jika alamat email terdaftar, permintaan sedang menunggu Administrator. Status akan diperbarui di sini jika permintaan dapat ditinjau.',
            ]);
    }

    public function test_requester_can_see_the_pending_status_in_the_same_session(): void
    {
        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email])
            ->assertSessionHas('reset_request_submitted')
            ->assertSessionHas('_old_input.email', $user->email);

        $this->get(route('password.request.status'))
            ->assertOk()
            ->assertJson([
                'state' => 'pending',
                'message' => 'Permintaan reset password Anda sedang menunggu persetujuan Administrator.',
            ]);

        $this->flushSession()
            ->get(route('password.request.status'))
            ->assertJson(['state' => 'idle']);
    }

    public function test_deactivated_account_cannot_submit_a_password_reset_request(): void
    {
        $user = User::factory()->create(['is_active' => false]);

        $this->post('/forgot-password', ['email' => $user->email])
            ->assertSessionHas('status');

        $this->assertDatabaseCount('password_reset_requests', 0);
    }

    public function test_repeated_forgot_password_submissions_do_not_duplicate_pending_requests(): void
    {
        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);
        $this->post('/forgot-password', ['email' => $user->email]);

        $this->assertDatabaseCount('password_reset_requests', 1);
    }

    public function test_admin_dosen_and_mahasiswa_can_all_submit_reset_requests(): void
    {
        foreach (['Admin', 'Dosen', 'Mahasiswa'] as $role) {
            $user = User::role($role)->firstOrFail();

            $this->actingAs($user)
                ->post(route('password.email'), ['email' => $user->email])
                ->assertSessionHas('status');
        }

        $this->assertDatabaseCount('password_reset_requests', 3);
    }

    public function test_approved_requester_can_see_the_new_password_form(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        $request = PasswordResetRequestModel::where('user_id', $user->id)->firstOrFail();
        $admin = User::role('Admin')->firstOrFail();

        Notification::assertNothingSent();

        $this->actingAs($admin)
            ->post(route('users.password-reset-requests.approve', $request))
            ->assertSessionHasNoErrors();

        $this->get(route('password.request.status'))
            ->assertOk()
            ->assertJson([
                'state' => 'approved',
                'message' => 'Permintaan disetujui. Silakan buat password baru melalui formulir di bawah ini.',
            ]);

        Notification::assertNothingSent();

        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Password baru')
            ->assertSee('Konfirmasi password baru')
            ->assertSee('class="approved-password-form" data-approved-password-form', false)
            ->assertSee('id="new_password" type="password" name="password" required autocomplete="new-password"', false)
            ->assertSee('autofocus >', false)
            ->assertSee(route('password.approved.store'));
    }

    public function test_approved_requester_can_set_password_and_complete_the_request(): void
    {
        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);
        $request = PasswordResetRequestModel::where('user_id', $user->id)->firstOrFail();
        $this->actingAs(User::role('Admin')->firstOrFail())
            ->post(route('users.password-reset-requests.approve', $request));

        $this->post(route('password.approved.store'), [
            'password' => 'NewStrongPassword123!',
            'password_confirmation' => 'NewStrongPassword123!',
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Password berhasil diperbarui. Silakan masuk menggunakan password baru.');

        $this->assertTrue(Hash::check('NewStrongPassword123!', $user->fresh()->password));
        $this->assertDatabaseHas('password_reset_requests', [
            'id' => $request->id,
            'status' => PasswordResetRequestModel::STATUS_COMPLETED,
        ]);
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);

        $this->post(route('password.approved.store'), [
            'password' => 'AnotherStrongPassword123!',
            'password_confirmation' => 'AnotherStrongPassword123!',
        ])->assertSessionHasErrors('password');
    }

    public function test_password_cannot_be_changed_before_request_approval(): void
    {
        $user = User::factory()->create();
        $originalPassword = $user->password;

        $this->post('/forgot-password', ['email' => $user->email]);

        $this->post(route('password.approved.store'), [
            'password' => 'NewStrongPassword123!',
            'password_confirmation' => 'NewStrongPassword123!',
        ])->assertSessionHasErrors('password');

        $this->assertSame($originalPassword, $user->fresh()->password);
    }

    public function test_password_can_still_be_reset_with_a_valid_legacy_email_token(): void
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewStrongPassword123!',
            'password_confirmation' => 'NewStrongPassword123!',
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('NewStrongPassword123!', $user->fresh()->password));
    }

    public function test_requester_sees_that_the_administrator_rejected_the_request(): void
    {
        $user = User::role('Mahasiswa')->firstOrFail();
        $this->post('/forgot-password', ['email' => $user->email]);
        $request = PasswordResetRequestModel::where('user_id', $user->id)->firstOrFail();

        $this->actingAs(User::role('Admin')->firstOrFail())
            ->post(route('users.password-reset-requests.reject', $request))
            ->assertSessionHasNoErrors();

        $this->get(route('password.request.status'))
            ->assertOk()
            ->assertJson([
                'state' => 'rejected',
                'message' => 'Permintaan reset password ditolak Administrator. Anda dapat menghubungi Admin jika membutuhkan bantuan.',
            ]);
    }

    public function test_requester_is_not_left_waiting_if_the_request_history_is_deleted(): void
    {
        $user = User::factory()->create();
        $this->post('/forgot-password', ['email' => $user->email]);
        $request = PasswordResetRequestModel::where('user_id', $user->id)->firstOrFail();
        $request->delete();

        $this->get(route('password.request.status'))
            ->assertJson([
                'state' => 'unavailable',
                'message' => 'Riwayat permintaan ini tidak lagi tersedia. Silakan ajukan permintaan baru atau hubungi Administrator.',
            ]);
    }

    public function test_forgot_password_page_can_be_opened_in_multiple_browsers_without_ip_throttling(): void
    {
        foreach (range(1, 8) as $attempt) {
            $this->get(route('password.request'))->assertOk();
        }
    }

    public function test_forgot_password_request_limit_is_scoped_to_the_email_not_shared_between_accounts(): void
    {
        foreach (range(1, 5) as $attempt) {
            $this->post(route('password.email'), ['email' => 'same@example.com'])
                ->assertRedirect();
        }

        $this->post(route('password.email'), ['email' => 'same@example.com'])
            ->assertTooManyRequests();

        $this->post(route('password.email'), ['email' => 'another@example.com'])
            ->assertRedirect();
    }
}
