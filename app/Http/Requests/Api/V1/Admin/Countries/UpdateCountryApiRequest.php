<?php

namespace App\Http\Requests\Api\V1\Admin\Countries;

use Illuminate\Foundation\Http\FormRequest;
use Gate;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class UpdateCountryApiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('country_update');
       // return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $id = request()->route('id');

        return [
            
            'country_name' => [
                'required',
                'string',
                //'unique:countries,country_name,NULL,id,deleted_at,NULL,'.$id,
                'unique:countries,country_name,'.$id.',id,deleted_at,NULL',
               // Rule::unique('countries')->where('id', '!=', $id)->whereNull('deleted_at'),
            ],

            'name_en' => [
                'required',
                'string',
    //'unique:countries,country_name,NULL,id,deleted_at,NULL,'.$id,
                'unique:countries,name_en,'.$id.',id,deleted_at,NULL',
            ],

            'country_code' => [
                'required',
                'string',
                //'unique:countries,country_code,'.$id,
                'unique:countries,country_code,'.$id.',id,deleted_at,NULL',
            ],
            
            'phonecode' => [
                'required',
                'integer',
                //'unique:countries,phonecode,'.$id,
                'unique:countries,phonecode,'.$id.',id,deleted_at,NULL',
            ],
        ];
    }
}
