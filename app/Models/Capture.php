<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 

class Capture extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'media_id',
        'url',
        'postion',
        'playtime',
        'rotate',
        'scale'
    ];  

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function media() {
        return $this->belongsTo(Media::class);
    }
}
