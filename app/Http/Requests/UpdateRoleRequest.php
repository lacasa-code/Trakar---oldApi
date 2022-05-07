<?php

namespace App\Http\Requests;

use App\Models\Role;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class UpdateRoleRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('role_edit');
    }

    public function rules()
    {
        $id = request()->route('id');
        return [
            'title'         => [
                'string',
                'required',
                'unique:roles,title,'.$id,
               // 'unique:roles,title,'.request()->route('role')->id,
            ],
            //'permissions.*' => [
             //   'integer',
           // ],
            'permissions'   => [
                'required',
               // 'array',
            ],
        ];
    }
}
