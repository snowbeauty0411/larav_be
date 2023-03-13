<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    use HasFactory;

    protected $table = 'service_categories';

    protected $fillable = [
        'name'
    ];

    // protected $casts = [
    //     'created_at' => 'datetime:Y-m-d H:i:s',
    //     'updated_at' => 'datetime:Y-m-d H:i:s',
    // ];

    public function getServiceCategoryById($id)
    {
        return $this->find($id);
    }

    public function getAllServiceCategory()
    {
        return $this->all();
    }

    public function findServiceCategoryByName($name)
    {
        return $this->where('name', $name)->first();
    }
}
