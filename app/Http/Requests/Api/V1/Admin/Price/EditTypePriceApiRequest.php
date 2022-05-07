<?php

namespace App\Http\Requests\Api\V1\Admin\Price;

use Illuminate\Foundation\Http\FormRequest;

class EditTypePriceApiRequest extends FormRequest
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
            
       /* 'product_id'      => [
            'required',
            'integer',
            'exists:products,id,deleted_at,NULL' // adited validation ahmed
        ],*/

        'producttype_id'  => [
            'required',
            'integer',
            'exists:producttypes,id,deleted_at,NULL' // adited validation ahmed
        ],

        'price'           => [
            'required',
            'numeric',
            'min:1',
        ],
        
        ];
    }
}
