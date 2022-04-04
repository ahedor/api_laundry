<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paket extends Model
{
    protected $fillable = ['id_paket', 'nama_paket'];
    protected $hidden = ['created_at', 'updated_at'];
    protected $table = "paket";
    protected $primaryKey = 'id_paket';
}
