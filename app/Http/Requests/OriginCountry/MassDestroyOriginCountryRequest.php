<?php

namespace App\Http\Requests\OriginCountry;

use Illuminate\Foundation\Http\FormRequest;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class MassDestroyOriginCountryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
      return Gate::allows('manufacturers_delete');
     // abort_if(Gate::denies('origin_countries_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        // return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'ids'   => 'required',
            // 'ids.*' => 'exists:car_mades,id',
        ];
    }
}
