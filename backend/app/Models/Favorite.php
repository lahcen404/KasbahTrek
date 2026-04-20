<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model {
    protected $fillable = ['traveler_id', 'tour_id'];
    public function traveler() { return $this->belongsTo(User::class, 'traveler_id'); }
    public function tour() { return $this->belongsTo(Tour::class); }
}
