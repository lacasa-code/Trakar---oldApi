<?php

namespace App\Http\Requests\Api\V1\User\Contact;

use Illuminate\Foundation\Http\FormRequest;

class ContactApiRequest extends FormRequest
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
            ],

            'email' => [
               'required',
                'email',
                'regex:/(.+)@(.+)\.(.+)/i',
            ],

            'phone_number' => [
               'required',
               'string', 
               'max:17',
            ],

            'message' => [
               'required',
               'string', 
            ],

            'subject' => [
               'required',
               'string', 
            ],

        ];
    }
}
