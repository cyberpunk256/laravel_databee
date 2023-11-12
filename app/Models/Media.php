<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 
use DateTimeInterface;

class Media extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'medias';   

    protected $fillable = [
        'admin_id',
        'name',
        'type',
        'video_duration',
        'media_path',
        'gpx_path',
        'image_lat',
        'image_long',
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

    public function admin() {
        return $this->belongsTo(Admin::class);
    }

    public function captures() {
        return $this->hasMany(Capture::class);
    }
}
