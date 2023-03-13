<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NoticePermit extends Model
{
    use HasFactory;

    protected $table = 'notice_permits';

    protected $fillable = [
        'notice_name',
        'notice_permit'
    ];
}
