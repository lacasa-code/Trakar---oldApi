<?php

namespace App\Http\Requests\Website\User;

use Illuminate\Foundation\Http\FormRequest;

class WebsiteLoginUserApiRequest extends FormRequest
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
            'email'    => 'required|string|email|regex:/(.+)@(.+)\.(.+)/i',
            'password' => 'required',
        ];
    }

    public function messages()
    {
        return [
                'email.email'        => __('ahmed.login_email_email'),
                'email.required'     => __('ahmed.login_email_required'),
                'email.regex'        => __('ahmed.login_email_regex'),
                'password.required'  => __('ahmed.login_password_required'),
        ];
    }
}
