<?php

namespace App\Http\Requests\Api\V1\User\Front;

use Illuminate\Foundation\Http\FormRequest;

class SelectCategoryApiRequest extends FormRequest
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
            
           /* 'category_id' => [
                'required',
                'integer',
                'exists:product_categories,id,deleted_at,NULL' // adited validation ahmed
            ], */

            'attribute' => [
                'required',
                //'integer',
                //'exists:product_categories,id,deleted_at,NULL' // adited validation ahmed
            ],

            'manufacturers' => [
                'nullable',
            ],

            'origins' => [
                'nullable',
            ],

            'start_price' => [
                'nullable',
                'numeric',
                'required_with:end_price',
                'before_or_equal:end_price'
            ],
            'end_price' => [
                'nullable',
                'numeric',
                'required_with:start_price',
                'after_or_equal:start_price',
            ],
            
        ];
    }
}
