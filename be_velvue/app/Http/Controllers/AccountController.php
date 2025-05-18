<?php

namespace App\Http\Controllers;

use App\Models\TemporaryUpload;
use App\Models\UserDashboardPreference;
use App\Rules\TemporaryFileExists;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AccountController extends Controller
{
    /**
     * Update the user's profile information.
     */
    public function update(Request $request): JsonResponse
    {
        $request->merge([
            // Remove extra spaces and non-word characters
            'name' => $request->name ? Str::squish(Str::onlyWords($request->name)) : '',
        ]);

        $user = $request->user();

        $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'avatar' => ['nullable', 'string', Rule::excludeIf($request->avatar === $user->avatar), 'regex:/^avatars\/[a-z0-9]{26}\.([a-z]++)$/i', new TemporaryFileExists],
        ]);

        if ($user->avatar && Str::startsWith($user->avatar, 'avatars/') && $user->avatar !== $request->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $email = $user->email;

        $user->update($request->only(['name', 'email', 'avatar']));

        if ($email !== $request->email) {
            $user->email_verified_at = null;
            $user->save(['timestamps' => false]);
            $user->sendEmailVerificationNotification();
        }

        // Delete temporary upload record
        TemporaryUpload::where('path', $request->avatar)->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Update the user's password.
     *
     * @throws ValidationException
     */
    public function password(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'string', 'min:8', 'max:100'],
            'password' => ['required', 'string', 'min:8', 'max:100', 'confirmed'],
        ]);

        $user = $request->user();
        abort_if(! $user->password, 403, __('Access denied.'));

        if (! Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => __('auth.password'),
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Update the user's dashboard preferences.
     */
    public function updateDashboardPreferences(Request $request): JsonResponse
    {
        $request->validate([
            'accent_color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'background_image_path' => ['nullable', 'string', new TemporaryFileExists],
        ]);

        $user = $request->user();
        $preference = $user->dashboardPreference ?? new UserDashboardPreference(['user_id' => $user->id]);

        // Handle background image
        if ($request->has('background_image_path') && $request->background_image_path !== $preference->background_image_path) {
            // Delete old background image if it exists
            if ($preference->background_image_path) {
                Storage::disk('public')->delete($preference->background_image_path);
            }

            // Set new background image if provided, otherwise set to null
            $preference->background_image_path = $request->background_image_path ?: null;

            // If new image path provided, delete from temporary uploads
            if ($request->background_image_path) {
                TemporaryUpload::where('path', $request->background_image_path)->delete();
            }
        }

        // Set accent color
        if ($request->has('accent_color')) {
            $preference->accent_color = $request->accent_color;
        }

        // Save or update preference
        $user->dashboardPreference()->save($preference);

        return response()->json([
            'success' => true,
            'dashboard_preference' => $preference,
        ]);
    }
}
