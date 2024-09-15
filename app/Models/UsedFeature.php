<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsedFeature extends Model
{
    use HasFactory;

    protected $fillable = [
        'feature_id',
        'user_id',
        'credits',
        'data',
    ];

    protected $casts = [
        'data' => 'array', // Corrected casting
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function feature() {
        return $this->belongsTo(Feature::class);
    }
}
