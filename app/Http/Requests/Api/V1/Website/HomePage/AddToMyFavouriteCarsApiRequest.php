<?php

namespace App\Http\Requests\Api\V1\Website\HomePage;

use Illuminate\Foundation\Http\FormRequest;

class AddToMyFavouriteCarsApiRequest extends FormRequest
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
            
            'car_type_id' => [
               'required',
               'integer', 
               'exists:cartypes,id,deleted_at,NULL' // adited validation ahmed 
            ],

            'car_made_id' => [
               'nullable',
               'integer', 
               'exists:car_mades,id,deleted_at,NULL' // adited validation ahmed 
            ],

            'car_model_id' => [
               'nullable',
               'integer', 
               'exists:car_models,id,deleted_at,NULL' // adited validation ahmed 
            ],

            'car_year_id' => [
               'nullable',
               'integer', 
               'exists:car_years,id,deleted_at,NULL' // adited validation ahmed 
            ],

            'transmission_id' => [
               'nullable',
               'integer', 
               'exists:transmissions,id,deleted_at,NULL' // adited validation ahmed 
            ],

        ];
    }
}
