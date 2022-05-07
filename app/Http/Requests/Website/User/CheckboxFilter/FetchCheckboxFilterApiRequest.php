<?php

namespace App\Http\Requests\Website\User\CheckboxFilter;

use Illuminate\Foundation\Http\FormRequest;

class FetchCheckboxFilterApiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
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
            'part_categories' => [
                'nullable',
            ],

            /*'categories' => [
                'nullable',
            ],*/

            'manufacturers' => [
                'nullable',
            ],

            'origins' => [
                'nullable',
            ],

            'start_price' => [
                'nullable',
                'numeric',
                'required_with:end_price',
                'before_or_equal:end_price'
            ],
            'end_price' => [
                'nullable',
                'numeric',
                'required_with:start_price',
                'after_or_equal:start_price',
            ],

        ];
    }
}
