<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDashboardPreference extends Model
{
    protected $fillable = [
        'user_id',
        'accent_color',
        'background_image_path',
    ];

    /**
     * Get the user that owns the dashboard preference.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
