<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes; 
use DateTimeInterface;

class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $guard = 'admin';    

    protected $fillable = [
        'name', 
        'email', 
        'password',
        'group_id',
        'role',
        'pref',
        'init_lat',
        'init_long',
        'deleted_at'
    ];    
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param  \DateTimeInterface  $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function medias() {
        return $this->hasMany(Media::class);
    }

    public function group() {
        return $this->belongsTo(Group::class);
    }
}
