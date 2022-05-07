<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminRejectVendorApiRequest extends FormRequest
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
            
            'vendor_id' => [
                'required',
                'integer',
                'exists:add_vendors,id,deleted_at,NULL' // adited validation ahmed
            ],

            'reason' => [
                'required',
                'string',
            ],

            'commented_field' => [
                'required',
          //  Rule::in('commercial_no', 'commercialDocs', 'tax_card_no', 'taxCardDocs', 'bank_account', 'bankDocs', 'type', 'company_name', 'wholesaleDocs'),
               // 'string',
            ],

        ];
    }
}
