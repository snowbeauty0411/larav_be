<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banks extends Model
{
    use HasFactory;

    protected $table = 'banks';

    protected $fillable = [
        'bank_id',
        'bank_name'
    ];

    public function getBank($request) {

        if ($request->name) {
            $result = $this->where('bank_name', 'like', '%' . $request->name . '%')->get();
        } else {
            $result = $this->get();
        }

        $banksList = array();
        $banks = array();
        $index = 0;
        foreach ($result as $item) {
            $banks[$index] = [
                'value' => $item->id,
                'text' => $item->bank_name
            ];
            array_push($banksList, $banks[$index]);
            $index++;
        }

        return $banksList;
    }
}
