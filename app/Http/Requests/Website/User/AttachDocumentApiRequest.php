<?php

namespace App\Http\Requests\Website\User;

use Illuminate\Foundation\Http\FormRequest;

class AttachDocumentApiRequest extends FormRequest
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

            'commercial' => [
                'required',
                'file',
                'mimes:pdf',
            ],

            'tax_card' => [
                'required',
                'file',
                'mimes:pdf',
            ],

            'bank_account' => [
                'required',
                'file',
                'mimes:pdf',
            ],

            'type'        => [
                'required',
                Rule::in('1','2', '3'),
            ],

        ];
    }
}
