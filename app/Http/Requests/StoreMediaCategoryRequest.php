<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Gate;
use Illuminate\Http\Response;
use App\Models\ProductCategory;

class StoreMediaCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
        //return Gate::allows('store_category_media');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => [
                'integer',
                'required',
                'exists:product_categories,id'
            ],
        ];
    }
}
