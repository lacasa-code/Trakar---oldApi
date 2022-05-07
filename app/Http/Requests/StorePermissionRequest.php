<?php

namespace App\Http\Requests;

use App\Models\Permission;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StorePermissionRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('permission_create');
    }

    public function rules()
    {
        return [
            'title' => [
                'string',
                'required',
                'unique:permissions,title',
            ],
        ];
    }

    public function messages()
    {
        return [
                'title.string'    => __('ahmed.permission_name_string'),
                'title.required'  => __('ahmed.permission_name_required'),
                'title.unique'    => __('ahmed.permission_name_unique'),
        ];
    }
}
