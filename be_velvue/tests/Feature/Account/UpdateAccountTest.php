<?php

namespace Tests\Feature\Account;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class UpdateAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_bio(): void
    {
        $user = User::factory()->create([
            'ulid' => Str::ulid()->toBase32(),
            'bio' => 'Original bio text',
        ]);

        $this->actingAs($user);

        $response = $this->postJson('/api/account/update', [
            'name' => $user->name,
            'email' => $user->email,
            'bio' => 'Updated bio text',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'bio' => 'Updated bio text',
        ]);
    }

    public function test_user_can_set_empty_bio(): void
    {
        $user = User::factory()->create([
            'ulid' => Str::ulid()->toBase32(),
        ]);

        $this->actingAs($user);

        $response = $this->postJson('/api/account/update', [
            'name' => $user->name,
            'email' => $user->email,
            'bio' => null,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'bio' => null,
        ]);
    }

    public function test_bio_cannot_exceed_maximum_length(): void
    {
        $user = User::factory()->create([
            'ulid' => Str::ulid()->toBase32(),
        ]);

        $this->actingAs($user);

        // Generate a string that exceeds the 255 character limit
        $longBio = Str::random(256);

        $response = $this->postJson('/api/account/update', [
            'name' => $user->name,
            'email' => $user->email,
            'bio' => $longBio,
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'errors' => [
                [
                    'path' => 'bio',
                    'message' => 'The bio field must not be greater than 255 characters.',
                ],
            ],
        ]);
    }

    public function test_bio_must_be_string(): void
    {
        $user = User::factory()->create([
            'ulid' => Str::ulid()->toBase32(),
        ]);

        $this->actingAs($user);

        $response = $this->postJson('/api/account/update', [
            'name' => $user->name,
            'email' => $user->email,
            'bio' => ['not', 'a', 'string'],
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'errors' => [
                [
                    'path' => 'bio',
                    'message' => 'The bio field must be a string.',
                ],
            ],
        ]);
    }
}
