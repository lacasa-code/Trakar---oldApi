<?php

namespace App\Http\Requests\Website\User\ProductReviews;

use Illuminate\Foundation\Http\FormRequest;

class AddReviewApiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        //return Gate::allows('reviews_add');
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

            'body_review' => [
                'required',
                'string',
               // 'unique:productreviews,body_review',
            ],

            /*'user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],*/

            'product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],

            'evaluation_value' => [
                'required',
                'numeric',
                'min:0.5',
                'max:5',
            ],

        ];
    }
}

