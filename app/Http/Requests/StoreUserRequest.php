<?php

namespace App\Http\Requests;

use App\Models\User;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'     => [
                'string',
                'required',
                'unique:users,name',
            ],
            'email'    => [
                'required',
                'email',
                'regex:/(.+)@(.+)\.(.+)/i',
                'unique:users,email,NULL,id,deleted_at,NULL',
            ],
            'password' => [
                'required',
               // 'confirmed',
                'min:8',
                // 'regex:/^.*(?=.{1,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%]).*$/',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*(_|[^\w])).+$/'
            ],
            //'roles.*'  => [
              //  'integer',
            //],
            'roles'    => [ // one role assigned
                'required',
                'integer',
                // 'exists:roles,id',
                'exists:roles,id,deleted_at,NULL' // adited validation ahmed
            ],
        ];
    }
        public function messages()
        {
            return [
                    'name.string'    => __('ahmed.user_name_string'),
                    'name.required'  => __('ahmed.user_name_required'),
                    'name.unique'    => __('ahmed.user_name_unique'),

                    'email.required' => __('ahmed.user_email_required'),
                    'email.email'    => __('ahmed.user_email_email'),
                    'email.regex'    => __('ahmed.user_email_email'),
                    'email.unique'   => __('ahmed.user_email_unique'),
            ];
        }
}
