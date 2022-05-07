<?php

namespace App\Http\Requests;

use App\Models\ProductCategory;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreProductCategoryRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('product_category_create');
    }

    public function rules()
    {
        return [
            'name' => [
                'string',
                'required',
                'unique:product_categories,name,NULL,id,deleted_at,NULL',
            ],

            'maincategory_id'  => [
                'integer',
                'required',
                'exists:maincategories,id,deleted_at,NULL' // adited validation ahmed
            ],

            'photo' => [
                'required',
                'file',
                'image'
            ],

            'name_en'      => [
                'required',
                'string',
                'unique:product_categories,name_en,NULL,id,deleted_at,NULL',
            ],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => __('ahmed.category_name_required'),
            'name.string'   => __('ahmed.category_name_string'),
            'name.unique'   => __('ahmed.category_name_unique'),
        ];
    }
}
