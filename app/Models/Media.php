<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    protected $table = 'medias';   

    protected $fillable = [
        'admin_id',
        'name',
        'type',
        'length',
        'url',
        'gpx_url',
    ];  

    public function Admin() {
        return $this->belongsTo(Admin::class);
    }

}
