<?php

namespace App\Http\Requests\Website\User\Ads;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class AdsCartypePlatfromApiRequest extends FormRequest
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
            
            'cartype_id' => [
                'required', 
                'integer',
                'exists:cartypes,id,deleted_at,NULL' // adited validation ahmed
            ],

            'platform' => [
                'required', 
                Rule::in('web','mobile'),
            ],

        ];
    }
}
