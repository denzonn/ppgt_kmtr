<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Linktree extends Model
{
    protected $table = 'linktree';

    protected $fillable = ['nama', 'link', 'urutan'];

    public function scopeTerurut($query)
    {
        return $query->orderBy('urutan')->orderBy('id');
    }
}
