<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDashboardPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'accent_color',
        'background_image_path',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
