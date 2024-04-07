<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = ['title','email', 'start', 'end', 'user', 'allDay'];
}
