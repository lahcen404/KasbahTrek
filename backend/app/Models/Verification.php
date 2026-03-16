<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Verification extends Model {
    protected $fillable = ['file_url', 'status', 'guide_id', 'admin_id'];
    protected $casts = ['status' => \App\Enums\Status::class];
    public function guide() { return $this->belongsTo(User::class, 'guide_id'); }
    public function admin() { return $this->belongsTo(User::class, 'admin_id'); }
}
