<?php

namespace Tests\Feature\Account;

use App\Models\TemporaryUpload;
use App\Models\User;
use App\Models\UserDashboardPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class DashboardPreferencesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        Storage::fake('public');
    }

    private function createTemporaryUploadedFile(string $entity, string $filename = 'test_image.webp'): string
    {
        $path = "$entity/" . Str::ulid()->toBase32() . '.webp';
        UploadedFile::fake()->image($filename)->storeAs($entity, basename($path), 'public');
        TemporaryUpload::create(['path' => $path]);

        return $path;
    }

    public function test_user_can_set_accent_color_preference(): void
    {
        $response = $this->postJson(route('account.dashboard-preferences'), [
            'accent_color' => '#123456',
        ]);

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonPath('dashboard_preference.accent_color', '#123456');
        $this->assertDatabaseHas('user_dashboard_preferences', [
            'user_id' => $this->user->id,
            'accent_color' => '#123456',
            'background_image_path' => null,
        ]);
    }

    public function test_user_can_set_background_image_preference(): void
    {
        $tempImagePath = $this->createTemporaryUploadedFile('dashboard_backgrounds');

        $response = $this->postJson(route('account.dashboard-preferences'), [
            'background_image_path' => $tempImagePath,
        ]);

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonPath('dashboard_preference.background_image_path', $tempImagePath);

        $this->assertDatabaseHas('user_dashboard_preferences', [
            'user_id' => $this->user->id,
            'background_image_path' => $tempImagePath,
        ]);
        $this->assertDatabaseMissing('temporary_uploads', ['path' => $tempImagePath]);
        $this->assertTrue(Storage::disk('public')->exists($tempImagePath));
    }

    public function test_user_can_update_both_preferences(): void
    {
        $tempImagePath = $this->createTemporaryUploadedFile('dashboard_backgrounds');
        $response = $this->postJson(route('account.dashboard-preferences'), [
            'accent_color' => '#ABCDEF',
            'background_image_path' => $tempImagePath,
        ]);
        $response->assertOk();
        $this->assertDatabaseHas('user_dashboard_preferences', [
            'user_id' => $this->user->id,
            'accent_color' => '#ABCDEF',
            'background_image_path' => $tempImagePath,
        ]);
    }

    public function test_updating_background_image_deletes_old_image_and_temporary_record(): void
    {
        // Set initial image
        $oldTempPath = $this->createTemporaryUploadedFile('dashboard_backgrounds', 'old_image.webp');
        $this->postJson(route('account.dashboard-preferences'), ['background_image_path' => $oldTempPath])->assertOk();
        $this->assertDatabaseHas('user_dashboard_preferences', ['user_id' => $this->user->id, 'background_image_path' => $oldTempPath]);
        $this->assertTrue(Storage::disk('public')->exists($oldTempPath));

        // Set new image
        $newTempPath = $this->createTemporaryUploadedFile('dashboard_backgrounds', 'new_image.webp');
        $this->postJson(route('account.dashboard-preferences'), ['background_image_path' => $newTempPath])->assertOk();

        $this->assertDatabaseHas('user_dashboard_preferences', ['user_id' => $this->user->id, 'background_image_path' => $newTempPath]);
        $this->assertTrue(Storage::disk('public')->exists($newTempPath));
        $this->assertFalse(Storage::disk('public')->exists($oldTempPath)); // Old image should be deleted
        $this->assertDatabaseMissing('temporary_uploads', ['path' => $newTempPath]); // New temp record deleted
        $this->assertDatabaseMissing('temporary_uploads', ['path' => $oldTempPath]); // Old temp record should have been deleted earlier
    }

    public function test_user_can_clear_accent_color(): void
    {
        UserDashboardPreference::factory()->create(['user_id' => $this->user->id, 'accent_color' => '#FF0000']);
        $response = $this->postJson(route('account.dashboard-preferences'), ['accent_color' => null]);
        $response->assertOk();
        $this->assertDatabaseHas('user_dashboard_preferences', ['user_id' => $this->user->id, 'accent_color' => null]);
    }

    public function test_user_can_clear_background_image_and_it_deletes_file(): void
    {
        $imagePath = 'dashboard_backgrounds/persisted_image.webp';
        Storage::disk('public')->put($imagePath, UploadedFile::fake()->image('persisted_image.webp')->getContent());
        UserDashboardPreference::factory()->create(['user_id' => $this->user->id, 'background_image_path' => $imagePath]);
        $this->assertTrue(Storage::disk('public')->exists($imagePath));

        $response = $this->postJson(route('account.dashboard-preferences'), ['background_image_path' => null]);
        $response->assertOk();

        $this->assertDatabaseHas('user_dashboard_preferences', ['user_id' => $this->user->id, 'background_image_path' => null]);
        $this->assertFalse(Storage::disk('public')->exists($imagePath));
    }

    public function test_accent_color_validation_fails_for_invalid_hex(): void
    {
        $response = $this->postJson(route('account.dashboard-preferences'), ['accent_color' => 'invalidcolor']);
        $response->assertStatus(422);
        $errors = collect($response->json('errors'));
        $this->assertTrue($errors->contains('path', 'accent_color'));

        $response = $this->postJson(route('account.dashboard-preferences'), ['accent_color' => '#12345']); // Too short
        $response->assertStatus(422);
        $errors = collect($response->json('errors'));
        $this->assertTrue($errors->contains('path', 'accent_color'));
    }

    public function test_background_image_path_validation_fails_for_non_existent_temporary_file(): void
    {
        $response = $this->postJson(route('account.dashboard-preferences'), ['background_image_path' => 'dashboard_backgrounds/nonexistent.webp']);
        $response->assertStatus(422);
        $errors = collect($response->json('errors'));
        $this->assertTrue($errors->contains('path', 'background_image_path'));
    }

    public function test_background_image_path_validation_fails_for_wrong_entity_regex(): void
    {
        $tempImagePath = $this->createTemporaryUploadedFile('avatars'); // Wrong entity
        $response = $this->postJson(route('account.dashboard-preferences'), ['background_image_path' => $tempImagePath]);
        $response->assertStatus(422);
        $errors = collect($response->json('errors'));
        $this->assertTrue($errors->contains('path', 'background_image_path'));
    }
}
