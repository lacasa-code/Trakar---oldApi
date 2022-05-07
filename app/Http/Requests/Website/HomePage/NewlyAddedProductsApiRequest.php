<?php

namespace App\Http\Requests\Website\HomePage;

use Illuminate\Foundation\Http\FormRequest;

class NewlyAddedProductsApiRequest extends FormRequest
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
           
            'from_date' => [
                'nullable',
                'date',
                'date_format:Y-m-d',
                'required_with:to_date',
                'before_or_equal:to_date',
            ],

            'to_date' => [
                'nullable',
                'date',
                'date_format:Y-m-d',
                'required_with:from_date',
                'after_or_equal:from_date',
            ],

            'cartype_id' => [
                'required',
                'integer',
                'exists:cartypes,id,deleted_at,NULL' // adited validation ahmed
            ],

        ];
    }
}
