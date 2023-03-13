<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyInfo extends Model
{
    use HasFactory;
    protected $table='company_info';

      /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'address',
        'tel',
        'fax',
        'establish',
        'capital',
        'customer_banks',
        'ceo',
        'director',
        'website_url',
        'business_content',
        'other'
    ];

    public function companyInfo(){
        $result = $this->first();
        return $result;
    }

}
