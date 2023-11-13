<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 
use DateTimeInterface;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'deleted_at'
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

    public function admins() {
        return $this->hasMany(Admin::class);
    }
    
    public function users() {
        return $this->hasMany(User::class);
    }
}
