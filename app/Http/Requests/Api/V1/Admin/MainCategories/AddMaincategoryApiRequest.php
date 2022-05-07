<?php

namespace App\Http\Requests\Api\V1\Admin\MainCategories;

use Illuminate\Foundation\Http\FormRequest;
use Gate;
use Illuminate\Http\Response;

class AddMaincategoryApiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
        return Gate::allows('main_category_create');
       // return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [

            'main_category_name'  => [
                'required',
                'string',
                'unique:maincategories,main_category_name,NULL,id,deleted_at,NULL',
            ],

            'name_en'  => [
                'required',
                'string',
                'unique:maincategories,name_en,NULL,id,deleted_at,NULL',
            ],
            
        ];
    }
}
