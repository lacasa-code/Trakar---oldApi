<?php

namespace App\Http\Resources\Api\Admin\Allcategory;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Api\Admin\Allcategory\HomeAllcategoryApiResource;
use App\Models\Allcategory;
use App\Http\Resources\Api\Admin\Allcategory\SiblingsApiResource;

class EditProductCatsApiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // return parent::toArray($request);

        return[
            'id'                 => $this->id,
            'name'               => $this->name,
            'name_en'            => $this->name_en,
            'allcategory_id'        => $this->allcategory_id,
           // 'level'   => $this->allcategories->count(),
          //  'catName' => $this->allcategory_id == null ? null : $this->catName($this->allcategory_id),
            'siblings' => SiblingsApiResource::collection(Allcategory::where('allcategory_id', $this->allcategory_id)->get()),
            'childs' => $this->allcategory_id == null ? null : SiblingsApiResource::collection(Allcategory::where('allcategory_id', $this->id)->get()),
            "media"              => $this->media,  
            "photo"              => $this->photo,
            'created_at'         => $this->created_at,
        ];

    }
}