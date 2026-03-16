<?php

namespace App\Models;

use App\Enums\DifficultyLevel;
use Illuminate\Database\Eloquent\Model;

class Tour extends Model {
    protected $fillable = ['title', 'description', 'location', 'price', 'difficulty', 'max_spots', 'current_bookings', 'guide_id'];

    protected $casts = [
        'difficulty' => DifficultyLevel::class,
        'price' => 'float',
    ];

    // relation wih guide
    public function guide() {
        return $this->belongsTo(User::class, 'guide_id');
    }

    // relation with 
    public function images() {
        return $this->hasMany(Image::class);
    }
}
