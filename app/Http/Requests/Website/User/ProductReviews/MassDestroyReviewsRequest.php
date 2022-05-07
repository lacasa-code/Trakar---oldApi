<?php

namespace App\Http\Requests\Website\User\ProductReviews;

use Illuminate\Foundation\Http\FormRequest;

class MassDestroyReviewsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // return Gate::allows('reviews_delete');
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
            'ids'   => 'required',
            // 'ids.*' => 'exists:stores,id',
        ];

    }
}
