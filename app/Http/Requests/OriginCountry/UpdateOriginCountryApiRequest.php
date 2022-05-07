<?php

namespace App\Http\Requests\OriginCountry;

use Illuminate\Foundation\Http\FormRequest;
use Gate;
use Illuminate\Validation\Rule;

class UpdateOriginCountryApiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('origin_countries_update');
        // return true;
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
               // 'unique:prodcountries,country_name,'. $id,
               // 'unique:prodcountries,country_name,NULL,id,project_id,'.$project->id,
                 Rule::unique('prodcountries')->ignore($id)->whereNull('deleted_at'),
            ],

            'name_en' => [
                'required',
                'string',
               // 'unique:prodcountries,country_name,'. $id,
               // 'unique:prodcountries,country_name,NULL,id,project_id,'.$project->id,
                 Rule::unique('prodcountries')->ignore($id)->whereNull('deleted_at'),
            ],
        ];
    }
}
