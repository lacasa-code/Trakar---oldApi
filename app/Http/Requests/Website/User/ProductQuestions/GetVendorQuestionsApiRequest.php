<?php

namespace App\Http\Requests\Website\User\ProductQuestions;

use Illuminate\Foundation\Http\FormRequest;

class GetVendorQuestionsApiRequest extends FormRequest
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
        ];
    }
}
