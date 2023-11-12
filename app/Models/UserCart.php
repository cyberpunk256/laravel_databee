<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 
use DateTimeInterface;

class UserCart extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'capture_id',
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

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function capture() {
        return $this->belongsTo(Capture::class);
    }
}
