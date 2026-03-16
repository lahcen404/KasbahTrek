<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripReport extends Model {
    protected $fillable = ['reason', 'status', 'traveler_id', 'tour_id', 'admin_id'];
    protected $casts = ['status' => \App\Enums\Status::class];
    public function traveler() { return $this->belongsTo(User::class, 'traveler_id'); }
    public function tour() { return $this->belongsTo(Tour::class); }
    public function admin() { return $this->belongsTo(User::class, 'admin_id'); }
}
