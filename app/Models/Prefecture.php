<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prefecture extends Model
{
    use HasFactory;

    protected $table = 'prefectures';

    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
   protected $fillable = [
       'area_id',
       'name'
   ];

   public function area()
   {
       return $this->belongsTo(Area::class, 'area_id');
   }

   public function getAreaByPrefectureName($pref_name)
   {
        return $this->where('name', $pref_name)->with('area')->first();
   }
}
