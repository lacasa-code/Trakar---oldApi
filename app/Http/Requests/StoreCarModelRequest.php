<?php

namespace App\Http\Requests;

use App\Models\CarModel;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreCarModelRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('car_model_create');
    }

    public function rules()
    {
        return [
            'carmade_id' => [
                'required',
                'integer',
                'exists:car_mades,id,deleted_at,NULL' // adited validation ahmed
            ],
            'carmodel'   => [
                'string',
                'required',
                //'unique:car_models,carmodel,NULL,id,deleted_at,NULL',
            ],

            'name_en'      => [
                'required',
                'string',
            ],
        ];
    }
}
