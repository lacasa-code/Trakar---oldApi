<?php

namespace App\Http\Requests\Website\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Gate;
use Illuminate\Http\Response;

class WebsiteRegisterUserApiRequest extends FormRequest
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
            'name'     => [
                'string',
                'required',
               // 'unique:users,name',
            ],
            'email'    => [
                'required',
                'email',
                'regex:/(.+)@(.+)\.(.+)/i',
                'unique:users,email,NULL,id,deleted_at,NULL',
               // 'unique:vendorstaffs,email,NULL,id,deleted_at,NULL',
            ],
            'password' => [
                'required',
                'confirmed',
                'min:8',
                // 'regex:/^.*(?=.{1,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%]).*$/',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*(_|[^\w])).+$/'
            ],

           /* 'role'    => [
                'required',
                'integer',
                Rule::in('2', '3'),
                'exists:roles,id,deleted_at,NULL' // adited validation ahmed
            ],*/
        ];
    }

    public function messages()
        {
            return [
                    'name.string'    => __('ahmed.user_name_string'),
                    'name.required'  => __('ahmed.user_name_required'),
                 //   'name.unique'    => __('ahmed.user_name_unique'),

                    'email.required' => __('ahmed.user_email_required'),
                    'email.email'    => __('ahmed.user_email_email'),
                    'email.regex'    => __('ahmed.user_email_email'),
                    'email.unique'   => __('ahmed.user_email_unique'),
            ];
        }
}
