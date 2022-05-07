<?php

namespace App\Http\Requests\Api\V1\User\Tickets;

use Illuminate\Foundation\Http\FormRequest;

class AddTicketApiRequest extends FormRequest
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
            
            //'user_id' => [],
           // 'lang' => [],
            'category_id' => [
                'required',
                'integer',
                'exists:ticketcategories,id,deleted_at,NULL' // adited validation ahmed
            ],
           // 'ticket_no' => [],
            'title' => [
                'required',
                'string',
            ],
            'priority' => [
                'nullable',
            ],
            'message' => [
                'required',
                'string',
            ],
            //'status' => [],
            'order_id' => [
                'required',
                'integer',
                'exists:orders,id,deleted_at,NULL' // adited validation ahmed
            ],
            'vendor_id' => [
                'required',
                'integer',
                'exists:add_vendors,id,deleted_at,NULL' // adited validation ahmed
            ],
        ];
    }
}
