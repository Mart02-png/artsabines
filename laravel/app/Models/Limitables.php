<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Limitables extends Model
{
    use HasFactory;
    protected $fillable = ['limittoday', 'start', 'end','email'];
}
