<?php

namespace App\Http\Requests;

use App\Models\PartCategory;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class MassDestroyPartCategoryRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('part_category_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'ids'   => 'required',
           // 'ids.*' => 'exists:part_categories,id',
        ];
    }
}
