<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $fillable = ['id_transaksi', 'id_member', 'tgl', 'batas_waktu', 'tgl_bayar', 'status', 'dibayar', 'id_user'];
    protected $hidden = ['created_at', 'updated_at'];
    protected $table = "transaksi";
    protected $primaryKey = 'id_transaksi';
}
