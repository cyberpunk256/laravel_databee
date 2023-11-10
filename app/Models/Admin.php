<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes; 

class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $guard = 'admin';    

    protected $fillable = [
        'name', 
        'email', 
        'password',
        'area_code',
        'ini_position',
        'deleted_at'
    ];    
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function medias() {
        return $this->hasMany(Media::class);
    }
}
