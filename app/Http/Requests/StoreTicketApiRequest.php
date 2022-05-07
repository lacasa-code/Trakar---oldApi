<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Gate;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class StoreTicketApiRequest extends FormRequest
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

            'title'       => [
                'required',
            ],

            'category_id' =>  [
                'required',
                'integer',
                'exists:ticketcategories,id,deleted_at,NULL' // adited validation ahmed
            ],

            'ticketpriority_id'    => [
                'required',
                'integer',
                'exists:ticketpriorities,id,deleted_at,NULL' // adited validation ahmed
                // Rule::in('low', 'medium', 'high'),
            ],

            'message'     => [
             'required',
            ],

            'order_id'  =>  [
                'required_with:vendor_id', 
                'required_with:product_id', 
                'integer',
                'exists:orders,id,deleted_at,NULL', // adited validation ahmed
            ],

            'vendor_id' => [
                'required_with:order_id', 
                'required_with:product_id', 
                'integer', 
                'exists:add_vendors,id,deleted_at,NULL' // adited validation ahmed
            ],

            'product_id' => [
                'required_with:vendor_id', 
                'required_with:order_id', 
                'integer', 
                'exists:products,id,deleted_at,NULL' // adited validation ahmed
            ],

            'attachment' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,gif,svg',
              //  'max:1048',
            ],

        ];
    }
}
