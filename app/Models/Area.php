<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    protected $table = 'areas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    public $incrementing = false;

    public function prefectures()
    {
        return $this->hasMany(Prefecture::class);
    }

    public function getAll(){
        return $this->with("prefectures")->get();
    }

}
