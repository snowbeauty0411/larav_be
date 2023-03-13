<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrivacyPolicy extends Model
{
    use HasFactory;
    protected $table='privacy_policies';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'text',
        'sort'
    ];
    

    public function listPrivacyPolicy(){
        $result=$this->get();
        return $result;
    }
}
