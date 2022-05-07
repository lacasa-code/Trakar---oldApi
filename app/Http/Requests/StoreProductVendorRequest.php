<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductVendorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('add_vendor_add_products');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
       return [
            'categories.*'     => [
                'integer',
            ],
            'categories'       => [
                'array',
            ],
            'car_made_id'      => [
                'required',
                'integer',
            ],
            'car_model_id'     => [
                'required',
                'integer',
            ],
            'year_id'          => [
                'required',
                'integer',
            ],
            'part_category_id' => [
                'required',
                'integer',
            ],
            'name'             => [
                'string',
                'required',
                'unique:products,name',
            ],
            'discount'         => [
                'nullable',
                'integer',
                'min:-2147483648',
                'max:2147483647',
            ],
            'price'            => [
                'required',
                'numeric',
            ],
        ];
    }
}
