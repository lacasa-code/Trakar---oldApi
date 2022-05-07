<?php

namespace App\Http\Requests\Website\User\Tickets;

use Illuminate\Foundation\Http\FormRequest;

class VendorAnswerTicketApiRequest extends FormRequest
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
            
            'ticket_id' => [
                'required',
                'integer',
                'exists:tickets,id,deleted_at,NULL' // adited validation ahmed
            ],

            'answer' => [
                'required',
                'string',
                'min:1',
                'max:250',
            ],
            
        ];
    }
}
