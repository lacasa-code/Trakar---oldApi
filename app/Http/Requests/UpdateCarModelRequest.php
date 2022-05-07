<?php

namespace App\Http\Requests;

use App\Models\CarModel;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class UpdateCarModelRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('car_model_edit');
    }

    public function rules()
    {
        $id = request()->route('id');
        return [
            'carmade_id' => [
                'required',
                'integer',
               // 'exists:car_mades,id',
                'exists:car_mades,id,deleted_at,NULL' // adited validation ahmed
            ],
            'carmodel'   => [
                'string',
                'required',
                // 'unique:car_models,carmodel,'. $id,
                // 'unique:car_models,carmodel,'. request()->route('carModel')->id,
            ],
            'name_en'      => [
                'required',
                'string',
            ],
        ];
    }
}
