<?php

namespace App\Http\Requests\Api\Admin\Allcategory;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreAllcategoryApiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // return Gate::allows('part_category_create');
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
            'name' => [
                'string',
                'required',
                // 'unique:allcategories,name,NULL,id,deleted_at,NULL',
            ],

            'description' => [
                'nullable',
                'string',
                'max:255',
            ],

            'allcategory_id' => [
                'required',
                'integer',
                'exists:allcategories,id,deleted_at,NULL' // adited validation ahmed
            ],

            'photo'         => [
                'required',
                'file',
                'image',
                'max:1048',
                'mimes:jpg,jpeg,gif,png',
            ],

            'name_en'      => [
                'required',
                'string',
              //  'unique:allcategories,name_en,NULL,id,deleted_at,NULL',
            ],

            'description_en' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }
}
