<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\UserDashboardPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserEndpointPreferencesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_user_endpoint_returns_null_dashboard_preference_if_not_set(): void
    {
        $response = $this->getJson(route('user'));
        $response->assertOk()
            ->assertJsonPath('user.dashboard_preference', null);
    }

    public function test_user_endpoint_returns_dashboard_preferences_when_set(): void
    {
        $prefs = UserDashboardPreference::factory()->create([
            'user_id' => $this->user->id,
            'accent_color' => '#AABBCC',
            'background_image_path' => 'dashboard_backgrounds/some_image.webp',
        ]);

        $response = $this->getJson(route('user'));

        $response->assertOk()
            ->assertJsonPath('user.dashboard_preference.accent_color', '#AABBCC')
            ->assertJsonPath('user.dashboard_preference.background_image_path', 'dashboard_backgrounds/some_image.webp');
    }
}
