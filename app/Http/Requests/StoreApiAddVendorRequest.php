<?php

namespace App\Http\Requests;

use App\Models\AddVendor;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class StoreApiAddVendorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('add_vendor_create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'vendor_name' => [
                'string',
                'required',
                'min:3',
                'unique:add_vendors,vendor_name',
            ],
            /*'email'       => [
                'required',
                'email',
                'unique:add_vendors,email',
                'unique:users,email',
                'regex:/(.+)@(.+)\.(.+)/i',
            ],*/
            'type'        => [
                'required',
                Rule::in('1','2', '3'),
            ],
            'userid_id'   => [
                'required',
                'integer',
               // 'exists:users,id',
                'exists:users,id,deleted_at,NULL' // adited validation ahmed
            ],
            'images'      => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg,gif,png',
            ],
            'serial'      => [
                'string',
                'nullable',
            ],
// new
            'commercial_no'      => [
                'required',
                'unique:add_vendors,commercial_no',
            ],

            'commercialDocs'      => [
                'required',
                'file',
                'mimes:pdf',
            ],

            'tax_card_no'      => [
                'required',
                'unique:add_vendors,tax_card_no',
            ],

            'taxCardDocs'      => [
                'required',
                'file',
                'mimes:pdf',
            ],

            'bank_account'      => [
                'required',
                'unique:add_vendors,bank_account',
            ],

        ];
    }
}
