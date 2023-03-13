<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UrlOfficial extends Model
{
    use HasFactory;

    protected $table = 'url_officials';

    protected $fillable = [
        'url_official',
        'url_facebook',
        'url_instagram',
        'url_twitter',
        'url_sns_1',
        'url_sns_2',
    ];

    // protected $casts = [
    //     'created_at' => 'datetime:Y-m-d H:i:s',
    //     'updated_at' => 'datetime:Y-m-d H:i:s',
    // ];

}
