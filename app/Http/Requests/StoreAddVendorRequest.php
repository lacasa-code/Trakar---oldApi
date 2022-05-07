<?php

namespace App\Http\Requests;

use App\Models\AddVendor;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class StoreAddVendorRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('add_vendor_create');
    }

    public function rules()
    {
        return [
            'vendor_name' => [
                'string',
                'required',
                'unique:add_vendors,vendor_name',
            ],
            'email'       => [
                'required',
                'email',
                'unique:add_vendors,email',
            ],
            'type'        => [
                'required',
                Rule::in('1','2', '3'),
            ],
            'userid_id'   => [
                'required',
                'integer',
                'exists:users,id',
            ],
            'images'      => [
                'required',
                // 'file',
                // 'image',
                // 'mimes:jpg,jpeg,gif,png',
            ],
            'serial'      => [
                'string',
                'nullable',
            ],
        ];
    }
}
