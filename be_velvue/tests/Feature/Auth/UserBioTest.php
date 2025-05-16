<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class UserBioTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_endpoint_returns_bio_field(): void
    {
        $user = User::factory()->create([
            'ulid' => Str::ulid()->toBase32(),
            'bio' => 'This is a test bio',
        ]);

        $this->actingAs($user);

        $response = $this->getJson('/api/user');

        $response->assertStatus(200);
        $response->assertJson(
            fn (AssertableJson $json) => $json
                ->where('success', true)
                ->has('user')
                ->where('user.bio', 'This is a test bio')
                ->etc()
        );
    }

    public function test_user_endpoint_returns_null_bio_when_empty(): void
    {
        $user = User::factory()->create([
            'ulid' => Str::ulid()->toBase32(),
            'bio' => null,
        ]);

        $this->actingAs($user);

        $response = $this->getJson('/api/user');

        $response->assertStatus(200);
        $response->assertJson(
            fn (AssertableJson $json) => $json
                ->where('success', true)
                ->has('user')
                ->where('user.bio', null)
                ->etc()
        );
    }

    public function test_bio_is_included_when_updating_user_info(): void
    {
        $user = User::factory()->create([
            'ulid' => Str::ulid()->toBase32(),
            'bio' => 'Initial bio',
        ]);

        $this->actingAs($user);

        // First update the bio
        $updateResponse = $this->postJson('/api/account/update', [
            'name' => $user->name,
            'email' => $user->email,
            'bio' => 'Updated bio content',
        ]);

        $updateResponse->assertStatus(200);

        // Then check if the user endpoint returns the updated bio
        $userResponse = $this->getJson('/api/user');

        $userResponse->assertStatus(200);
        $userResponse->assertJson(
            fn (AssertableJson $json) => $json
                ->where('success', true)
                ->has('user')
                ->where('user.bio', 'Updated bio content')
                ->etc()
        );
    }
}
