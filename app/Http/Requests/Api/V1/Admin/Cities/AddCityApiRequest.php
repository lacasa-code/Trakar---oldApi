<?php

namespace App\Http\Requests\Api\V1\Admin\Cities;

use Illuminate\Foundation\Http\FormRequest;
use Gate;
use Illuminate\Http\Response;

class AddCityApiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('city_create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            
            'city_name' => [
                'required',
                'string',
                'unique:cities,city_name,NULL,id,deleted_at,NULL',
            ],

            'name_en' => [
                'required',
                'string',
                'unique:cities,name_en,NULL,id,deleted_at,NULL',
            ],

            'area_id' => [
                'required',
                'integer',
                'exists:areas,id,deleted_at,NULL' // adited validation ahmed
            ],

        ];
    }
}
