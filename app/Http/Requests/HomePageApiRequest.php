<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HomePageApiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
         return [
            
            'search_index' => [
                'required',
            ],

            'cartype_id' => [
                'required',
                'integer',
                'exists:cartypes,id,deleted_at,NULL' // adited validation ahmed
            ],

        ];
    }

   /* public function messages()
    {
        return [
         'username.required' => __trans('translations.'),
        ];
    }*/
}
