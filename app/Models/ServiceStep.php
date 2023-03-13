<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceStep extends Model
{
    use HasFactory;

    protected $table = 'service_steps';

    protected $fillable = [
        'service_id',
        'number',
        'title',
        'content'
    ];
}
