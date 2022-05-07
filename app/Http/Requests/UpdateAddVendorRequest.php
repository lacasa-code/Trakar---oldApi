<?php

namespace App\Http\Requests;

use App\Models\AddVendor;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule; 

class UpdateAddVendorRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('add_vendor_edit');
    }

    public function rules()
    {
        return [
            'vendor_name' => [
                'string',
                'required',
                'unique:add_vendors,vendor_name,'.request()->route('addVendor')->id,
            ],
            'email'       => [
                'required',
                'email',
                // 'unique:users,email',
                'unique:add_vendors,email,'.request()->route('addVendor')->id,
            ],
            'type'        => [
                'required',
                Rule::in('1','2', '3'),
            ],
            'userid_id'   => [
                'required',
                'integer',
                //'exists:users,id',
                'exists:users,id,deleted_at,NULL' // adited validation ahmed
            ],

            // new
            'commercial_no'      => [
                'required',
                'unique:add_vendors,commercial_no,'.request()->route('addVendor')->id,
            ],

            'commercialDocs'      => [
                'nullable',
                'file',
                'mimes:pdf',
            ],

            'tax_card_no'      => [
                'required',
                'unique:add_vendors,tax_card_no,'.request()->route('addVendor')->id,
            ],

            'taxCardDocs'      => [
                'nullable',
                'file',
                'mimes:pdf',
            ],

            'bank_account'      => [
                'required',
                'unique:add_vendors,bank_account,'.request()->route('addVendor')->id,
            ],
        ];
    }
}
