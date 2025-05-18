<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Rules\TemporaryFileExists;

class AccountController extends Controller
{
    public function updateDashboardPreferences(Request $request)
    {
        $request->validate([
            'accent_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'background_image_path' => ['nullable', new TemporaryFileExists],
        ]);

        $user = $request->user();
        $preferences = $user->dashboardPreference()->firstOrNew();

        if ($request->filled('background_image_path')) {
            // Handle image processing and deletion of old images
            $this->processBackgroundImage($preferences, $request->background_image_path);
        }

        $preferences->accent_color = $request->accent_color;
        $preferences->save();

        return response()->json(['message' => 'Preferences updated successfully.']);
    }

    private function processBackgroundImage($preferences, $newImagePath)
    {
        // Delete old image if exists
        if ($preferences->background_image_path) {
            Storage::delete($preferences->background_image_path);
        }

        // Move new image from temporary storage
        $temporaryUpload = \App\Models\TemporaryUpload::where('path', $newImagePath)->first();
        if ($temporaryUpload) {
            Storage::move($temporaryUpload->path, 'dashboard_backgrounds/' . basename($temporaryUpload->path));
            $preferences->background_image_path = 'dashboard_backgrounds/' . basename($temporaryUpload->path);
            $temporaryUpload->delete();
        }
    }
}
