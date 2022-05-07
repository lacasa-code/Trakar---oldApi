<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Product;
use Gate;
use Illuminate\Http\Response;

class RemoveSpecificProductMediaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('product_edit');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [

            'product_id'     => [
                'required',
                'integer',
                'exists:products,id',
            ],
            'media_id'          => [
                'required',
                'integer',
                'exists:media,id',
            ],

        ];
    }
}
