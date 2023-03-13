<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class TermOfService extends Model
{
    use HasFactory;
    protected $table = 'term_of_services';

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

    public function listTermOfServices(){
        $query = $this->select(
         '*' 
        );
        $result=$query->orderBy('sort')->get();
        return $result;
    }
}
