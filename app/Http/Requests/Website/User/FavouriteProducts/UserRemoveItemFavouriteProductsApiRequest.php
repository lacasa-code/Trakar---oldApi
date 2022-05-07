<?php

namespace App\Http\Requests\Website\User\FavouriteProducts;

use Illuminate\Foundation\Http\FormRequest;

class UserRemoveItemFavouriteProductsApiRequest extends FormRequest
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

           'product_id' => [
           'required',
           'integer', 
           'exists:favouriteproducts,product_id,deleted_at,NULL' // adited validation ahmed 
           ],
           
        ];
    }
}
