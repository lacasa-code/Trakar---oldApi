<?php

namespace App\Http\Requests\Website\User\FavouriteCars;

use Illuminate\Foundation\Http\FormRequest;

class RemoveUserFavouriteCarApiRequest extends FormRequest
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
            
            'id' => [
                'required',
                'integer',
                'exists:favouritecars,id',
            ],
            
        ];
    }
}
