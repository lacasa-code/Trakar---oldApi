<?php

namespace App\Http\Requests\Api\V1\Admin\MainCategories;

use Illuminate\Foundation\Http\FormRequest;
use Gate;
use Illuminate\Http\Response;

class MassDestroyMaincategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('main_category_delete');
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
            'ids'   => 'required',
            // 'ids.*' => 'exists:products,id',
        ];
    }
}
