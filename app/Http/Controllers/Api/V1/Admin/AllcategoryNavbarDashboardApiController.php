<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\Api\Admin\Allcategory\AllcategoryApiResource;
use App\Models\Allcategory;
use Gate;
use Symfony\Component\HttpFoundation\Response;
use Auth;
use App\Http\Requests\SearchApisRequest;
use App\Http\Resources\Api\Admin\Allcategory\AllcategoryListApiResource;
use App\Http\Requests\Api\Admin\Allcategory\AllcategoryMarkNavbarApiRequest;

class AllcategoryNavbarDashboardApiController extends Controller
{
	public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }

    // start list all
     public function navbar_list_all($id)
     {
        $lang = $this->getLang();
        $onecategory = Allcategory::findOrFail($id);
        $cats     = [$id, 7];
        $data     = Allcategory::whereIn('allcategory_id', $cats)->get();
        foreach ($data as $value) {
          if($value->car_navbar != null && $value->commercial_navbar == null){
            $got_seq = $value->sequence;
            $commercial_seq = 0;
          }elseif($value->commercial_navbar != null && $value->car_navbar == null){
            $got_seq = $value->commercial_sequence;
            $commercial_seq = 0;
          }elseif($value->commercial_navbar != null && $value->car_navbar != null){
            $got_seq = $value->sequence;
            $commercial_seq = $value->commercial_sequence;
          }
          else{
            $got_seq = $value->sequence;
            $commercial_seq = 0;
          }
          $value['got_seq'] = $got_seq;
          $value['commercial_seq'] = $commercial_seq;
        }
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
          'data' => $data], Response::HTTP_OK);
     }
     // end list all 

    // start list all
     public function mark_navbar(AllcategoryMarkNavbarApiRequest $request)
     {
        $lang = $this->getLang();
        $id = $request->id;
        $navbars = json_decode($request->navbars);

        if ($id == 1) {
            Allcategory::where('car_navbar', $id)->where('navbar', 1)->update([
               'navbar' => 0,
               'car_navbar' => 0,
            ]);
         $car_navbar = $id;
        // $commercial_navbar = NULL;
             foreach ($navbars as $key => $value) {
                  Allcategory::where('id', $value)->update([
                  'navbar' => 1,
                  'sequence' => $key + 1,
                  'car_navbar' => $car_navbar,
                 // 'commercial_navbar' => $commercial_navbar,
                ]);
            }
        }
        if ($id == 3) {
          Allcategory::where('commercial_navbar', $id)->where('navbar', 1)->update([
               'navbar' => 0,
               'commercial_navbar' => 0,
            ]);
        //  $car_navbar = NULL;
          $commercial_navbar = $id;
              foreach ($navbars as $key => $value) {
                  Allcategory::where('id', $value)->update([
                  'navbar' => 1,
                  'commercial_sequence' => $key + 1,
                 // 'car_navbar' => $car_navbar,
                  'commercial_navbar' => $commercial_navbar,
                ]);
            }
        }
        
        return response()->json([
          'status_code'     => 200,
          'message'         => 'marked successfully',
          'data' => 'success'], Response::HTTP_OK);
     }
     // end list all 
}
