<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Gate;
use Illuminate\Http\Response;
use App\Models\Product;

class StoreMediaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
      //return Gate::allows('store_product_media');
    }

    public function rules()
    {
        return [
            'id' => [
                'integer',
                'required',
                'exists:products,id'
            ],
        ];
    }
}
