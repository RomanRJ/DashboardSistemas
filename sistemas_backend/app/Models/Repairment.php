<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Repairment extends Model
{
    protected $table = 'reparacion';
    public $timestamps = false;
    use HasFactory;
}
