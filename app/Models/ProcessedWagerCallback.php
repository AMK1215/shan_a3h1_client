<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessedWagerCallback extends Model
{
    use HasFactory;
    protected $fillable = ['wager_code'];
}
