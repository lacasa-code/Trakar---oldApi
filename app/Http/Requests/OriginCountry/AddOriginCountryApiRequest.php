<?php

namespace App\Http\Requests\OriginCountry;

use Illuminate\Foundation\Http\FormRequest;
use Gate;

class AddOriginCountryApiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('origin_countries_add');
        // return true;
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
                'unique:prodcountries,country_name,NULL,id,deleted_at,NULL',
                // 'unique:areas,area_name,NULL,id,deleted_at,NULL',
            ],

            'name_en' => [
                'required',
                'string',
                'unique:prodcountries,name_en,NULL,id,deleted_at,NULL',
                // 'unique:areas,area_name,NULL,id,deleted_at,NULL',
            ],
    
        ];
    }
}
