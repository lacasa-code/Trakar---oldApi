<?php

namespace App\Http\Requests;

use App\Models\Product;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreProductRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('product_create');
    }

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
