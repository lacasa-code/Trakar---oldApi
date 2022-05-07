<?php

namespace App\Http\Requests\Website\User\ProductReviews;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReviewApiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        //return Gate::allows('reviews_update');
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $id = request()->route('id');
        return [

            'body_review' => [
                'required',
                'string',
                'unique:productreviews,body_review,'. $id,
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

        ];
    }
}

