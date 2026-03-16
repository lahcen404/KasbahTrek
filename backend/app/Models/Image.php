<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model {
    protected $fillable = ['path', 'tour_id'];
    public function tour() { return $this->belongsTo(Tour::class); }
}
