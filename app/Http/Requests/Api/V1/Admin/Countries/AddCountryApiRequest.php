<?php

namespace App\Http\Requests\Api\V1\Admin\Countries;

use Illuminate\Foundation\Http\FormRequest;
use Gate;
use Illuminate\Http\Response;

class AddCountryApiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('country_create');
        // return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            
            'country_name' => [
                'required',
                'string',
                'unique:countries,country_name,NULL,id,deleted_at,NULL',
            ],

            'name_en' => [
                'required',
                'string',
                'unique:countries,name_en,NULL,id,deleted_at,NULL',
            ],

            'country_code' => [
                'required',
                'string',
                'unique:countries,country_code,NULL,id,deleted_at,NULL',
            ],
            
            'phonecode' => [
                'required',
                'integer',
                'unique:countries,phonecode,NULL,id,deleted_at,NULL',
            ],
        ];
    }
}
