<?php

namespace App\Http\Requests;

use App\Models\ProductCategory;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class UpdateProductCategoryRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('product_category_edit');
    }

    public function rules()
    {
        return [

            'name' => [
                'string',
                'required',
                'unique:product_categories,name,'. request()->route('productCategory')->id,
            ],

            'maincategory_id'  => [
                'integer',
                'required',
                'exists:maincategories,id,deleted_at,NULL' // adited validation ahmed
            ],

            'name_en'      => [
                'required',
                'string',
                'unique:product_categories,name_en,'. request()->route('productCategory')->id,
            ],

        ];
    }
}
