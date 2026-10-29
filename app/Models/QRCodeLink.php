<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QRCodeLink extends Model
{
    protected $table = 'qr_code_link';

    protected $fillable = [
        'nama',
        'link',
        'qr_code',
    ];
}
