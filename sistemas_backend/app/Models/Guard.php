<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guard extends Model
{
    protected $table = 'resguardos';
    public $timestamps = false;
    use HasFactory;
}
