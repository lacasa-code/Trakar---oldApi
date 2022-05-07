<?php

namespace App\Http\Requests;

use App\Models\PartCategory;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class UpdatePartCategoryRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('part_category_edit');
    }

    public function rules()
    {
        return [
            'category_name' => [
                'string',
                'required',
                'unique:part_categories,category_name,'.request()->route('partCategory')->id,
            ],

            'category_id' => [
                'required',
                'integer',
                'exists:product_categories,id,deleted_at,NULL' // adited validation ahmed
            ],

             'name_en' => [
                'string',
                'required',
                'unique:part_categories,name_en,'.request()->route('partCategory')->id,
            ],
        ];
    }
}
