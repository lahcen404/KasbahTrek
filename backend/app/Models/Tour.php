<?php

namespace App\Models;

use App\Enums\DifficultyLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tour extends Model
{

    use HasFactory;

    protected $fillable = [
        'title', 'description', 'location', 'price',
        'difficulty', 'max_spots', 'duration_hours', 'current_bookings', 'guide_id', 'category_id',
    ];

    protected $casts = [
        'difficulty' => DifficultyLevel::class,
        'price' => 'float',
        'duration_hours' => 'integer',
    ];

    public function guide() { return $this->belongsTo(User::class, 'guide_id'); }
    public function category() { return $this->belongsTo(Category::class); }
    public function images() { return $this->hasMany(Image::class); }
    public function bookings() { return $this->hasMany(Booking::class); }
    public function reviews() { return $this->hasMany(Review::class); }
    public function favorites() { return $this->hasMany(Favorite::class); }
    public function tripReports() { return $this->hasMany(TripReport::class); }
}
