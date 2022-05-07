<?php

namespace App\Http\Requests;

use App\Models\CarMade;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class UpdateCarMadeRequest extends FormRequest
{
    public function authorize()
    {
        //$id = $this->route('id');
        return Gate::allows('car_made_edit');
    }

    public function rules()
    {
        $id = request()->route('id');

        return [
            'cartype_id' => [
                'required',
                'integer',
                'exists:cartypes,id,deleted_at,NULL' // adited validation ahmed

            ],
            
            'car_made'      => [
                'required',
                'string',
                // 'regex:/(^[A-Za-z0-9 ]+$)+/', // letters numbers and spaces
                // 'unique:car_mades,car_made,'. $id,
            ],

            'name_en'      => [
                'required',
                'string',
            ],

        ];
    }
}
