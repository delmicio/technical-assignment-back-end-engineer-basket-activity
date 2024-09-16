<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Basket extends Model
{
    use HasFactory;

    protected $fillable = ['session_id', 'items', 'removed_items'];

    protected $casts = [
        'items' => 'array',
        'removed_items' => 'array',
    ];
}
