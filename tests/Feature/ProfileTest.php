<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone' => '08123456789',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertSame('08123456789', $user->phone);
        $this->assertNull($user->email_verified_at);
    }

    public function test_profile_update_shows_a_success_notification(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch('/profile', [
                'name' => 'Updated User',
                'email' => $user->email,
            ])
            ->assertRedirect('/profile')
            ->assertSessionHas('status', 'profile-updated');
    }

    public function test_user_can_upload_and_delete_their_profile_photo(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => UploadedFile::fake()->image('profile.jpg'),
        ])->assertRedirect('/profile');

        $avatarPath = $user->fresh()->avatar_path;
        $this->assertNotNull($avatarPath);
        Storage::disk('public')->assertExists($avatarPath);

        $this->delete(route('profile.avatar.destroy'))
            ->assertRedirect('/profile')
            ->assertSessionHas('status', 'avatar-deleted');

        $this->assertNull($user->fresh()->avatar_path);
        Storage::disk('public')->assertMissing($avatarPath);
    }

    public function test_profile_photo_preview_and_delete_confirmation_are_available_for_users_with_a_photo(): void
    {
        Storage::fake('public');
        $avatarPath = UploadedFile::fake()->image('profile.jpg')->store('avatars', 'public');
        $user = User::factory()->create(['avatar_path' => $avatarPath]);

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk()
            ->assertSee('data-open-avatar', false)
            ->assertSee('avatarPreviewDialog', false)
            ->assertSee('data-open-delete-avatar', false)
            ->assertSee('deleteAvatarDialog', false);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
