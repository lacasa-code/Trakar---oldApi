<?php

namespace App\Http\Requests;

use App\Models\CarMade;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreCarMadeRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('car_made_create');
    }

    public function rules()
    {
        return [
            
            'cartype_id' => [
                'required',
                'integer',
                //'exists:product_categories,id',
                'exists:allcategories,id,deleted_at,NULL' // adited validation ahmed
            ],
            
            'car_made'      => [
                'required',
                'string',
              // 'regex:/(^[A-Za-z0-9 ]+$)+/', // letters numbers and spaces
                //'unique:car_mades,car_made,NULL,id,deleted_at,NULL',
            ],

            'name_en'      => [
                'required',
                'string',
            ],
            
        ];
    }
}
