<?php

namespace App\Http\Requests;

use App\Models\User;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
        // return Gate::allows('user_edit');
    }

    public function rules()
    {
        return [
            'name'    => [
                'string',
                'required',
            ],
            'email'   => [
                'required',
                'email',
                // 'unique:users,email,' . request()->route('user')->id,
                Rule::unique('users')->ignore(request()->route('user')->id)->whereNull('deleted_at'),
            ],
           // 'roles.*' => [
             //   'integer',
           // ],
            'roles'   => [
                'required',
                'integer',
                //'exists:roles,id',
                'exists:roles,id,deleted_at,NULL' // adited validation ahmed
            ],

            'stores'    => [
                'required',
                // Rule::in('Manager', 'Staff'),
            ],
        ];
    }
}
