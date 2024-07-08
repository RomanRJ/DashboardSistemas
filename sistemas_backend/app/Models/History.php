<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Guard;
use App\Models\Item;

class History extends Model
{
    protected $table = 'articulo_por_resguardo';
    public $timestamps = false;

    public function inventario()
    {
        return $this->belongsTo(Item::class, 'idActivo_FK', 'id');
    }

    public function resguardo()
    {
        return $this->belongsTo(Guard::class, 'idResguardo_FK', 'id');
    }
}
