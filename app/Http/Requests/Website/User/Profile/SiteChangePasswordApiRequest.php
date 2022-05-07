<?php

namespace App\Http\Requests\Website\User\Profile;

use Illuminate\Foundation\Http\FormRequest;

class SiteChangePasswordApiRequest extends FormRequest
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

            'email' => [
                'required',
                'string',
                'email',
               // 'exists:users,email,deleted_at,NULL' // adited validation ahmed
            ],

          /*  'current_password' => [
                'required', 
                'string',
            ],

            'new_password' => [
                'required',
                'confirmed',
                'min:8',
                // 'regex:/^.*(?=.{1,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%]).*$/',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*(_|[^\w])).+$/'
            ],*/

        ];
    }
}
