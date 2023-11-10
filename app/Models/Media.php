<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 

class Media extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'medias';   

    protected $fillable = [
        'admin_id',
        'name',
        'type',
        'video_time',
        'media_path',
        'gpx_path',
        'image_lat',
        'image_long',
        'deleted_at'
    ];  

    public function admin() {
        return $this->belongsTo(Admin::class);
    }

    public function captures() {
        return $this->hasMany(Capture::class);
    }
}
