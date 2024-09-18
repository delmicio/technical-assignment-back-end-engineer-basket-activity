<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Basket extends Model
{
    use HasFactory;

    protected $fillable = ['session_id', 'items', 'removed_items', 'user_id'];

    protected $casts = [
        'items' => 'array',
        'removed_items' => 'array',
    ];

    /**
     * Get the user that owns the basket.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
