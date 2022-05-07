<?php

namespace App\Http\Requests\Website\User\Products;

use Illuminate\Foundation\Http\FormRequest;

class AddFavouriteCarMadeApiRequest extends FormRequest
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
            
            'car_made_id' => [
                'required',
                'integer', 
                'exists:car_mades,id',
            ]
            
        ];
    }
}
