<?php

namespace App\Http\Requests\Api\Admin\Allcategory;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class UpdateAllcategoryApiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // return Gate::allows('all_category_edit');
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
                'required',
                'string',
              //  'unique:allcategories,name,'.request()->route('allcategory')->id,
            ],

            'allcategory_id' => [
                'required',
                'integer',
                'exists:allcategories,id,deleted_at,NULL' // adited validation ahmed
            ],

             'name_en' => [
                'required',
                'string',
               // 'unique:allcategories,name_en,'.request()->route('allcategory')->id,
            ],
        ];
    }
}
