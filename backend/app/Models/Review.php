<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model {
    protected $fillable = ['rating', 'comment', 'traveler_id', 'tour_id'];
    public function traveler() { return $this->belongsTo(User::class, 'traveler_id'); }
    public function tour() { return $this->belongsTo(Tour::class); }
}
