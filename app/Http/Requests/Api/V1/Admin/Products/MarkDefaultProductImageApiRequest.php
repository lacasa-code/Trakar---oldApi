<?php

namespace App\Http\Requests\Api\V1\Admin\Products;

use Illuminate\Foundation\Http\FormRequest;

class MarkDefaultProductImageApiRequest extends FormRequest
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
            
            'product_id' => [
                'required',
                'integer',
                'exists:products,id,deleted_at,NULL' // adited validation ahmed
            ],

            'media_id' => [
                'required',
                'integer',
                // 'exists:products,id,deleted_at,NULL' // adited validation ahmed
            ],
        ];
    }
}
