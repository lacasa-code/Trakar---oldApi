<?php

namespace App\Http\Requests;

use App\Models\PartCategory;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StorePartCategoryRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('part_category_create');
    }

    public function rules()
    {
        return [
            'category_name' => [
                'string',
                'required',
                'unique:part_categories,category_name,NULL,id,deleted_at,NULL',
            ],

            'category_id' => [
                'required',
                'integer',
                'exists:product_categories,id,deleted_at,NULL' // adited validation ahmed
            ],

            'photo'         => [
                'required',
                'file',
                'image'
                 // 'mimes:jpg,jpeg,gif,png',
            ],

            'name_en'      => [
                'required',
                'string',
                'unique:part_categories,name_en,NULL,id,deleted_at,NULL',
            ],
        ];
    }
}
