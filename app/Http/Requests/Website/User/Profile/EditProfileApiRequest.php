<?php

namespace App\Http\Requests\Website\User\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Gate;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule; 

class EditProfileApiRequest extends FormRequest
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
            
            'name' => [
                'required',
                'string',
                'min:3',
            ],

            'last_name' => [
                'nullable',
                'string',
                'min:3',
            ],

            'email' => [
                'required',
                'string',
                'email',
                'regex:/(.+)@(.+)\.(.+)/i',
            ],

            'phone_no' => [
                'required',
              //  'numeric',
                'min:10',
                'max:17',
            ],

            'birthdate' => [
                'required',
                'date',
                'date_format:Y-m-d',
                'before:' . date('Y-m-d'),
            ],

            'gender' => [
                'required',
                'string',
                Rule::in('male', 'female'),
            ],
        ];
    }
}
