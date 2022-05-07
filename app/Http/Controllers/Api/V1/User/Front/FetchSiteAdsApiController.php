<?php

namespace App\Http\Controllers\Api\V1\User\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Advertisement;
use App\Models\Adpositions;
use Auth;
use Gate;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Website\User\Ads\AdsCartypePlatfromApiRequest;

class FetchSiteAdsApiController extends Controller
{
  public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }

    public function fetch_site_ads(Request $request)
    {
      $lang = $this->getLang();
      
      $data  = Advertisement::with(['car_type', 'adv_position'])->get();
      $total = Advertisement::count();
    	return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => $data,
            'total'         => $total,
          ], Response::HTTP_OK);
    }

    public function show($id)
    {
      $lang = $this->getLang();
      $data = Advertisement::findOrFail($id);
      $data['car_type_name']    = $data->car_type->type_name;
      $data['ad_position_name'] = $data->adv_position->position_name;
    	return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => $data
          ], Response::HTTP_OK);
    }

    public function dynamic_cartype_ads($id)
    {
       $lang = $this->getLang();
      // $advertisements  = Advertisement::where('cartype_id', $id)->get();
      // $total           = Advertisement::where('cartype_id', $id)->count();
      $ad_positions    = Adpositions::get();
      $data = array();
      foreach ($ad_positions as $ad_position) {
         array_push($data, [
          $ad_position->position_name => Advertisement::with(['car_type', 'adv_position'])
                                            ->where('cartype_id', $id)
                                            ->where('ad_position', $ad_position->id)
                                            ->get(),
         ]);
      }
      return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => $data,
           // 'total'         => $total
          ], Response::HTTP_OK);
    }

    public function show_position_ads($id)
    {
      $data = Advertisement::with(['car_type', 'adv_position'])->where('ad_position', $id)->get();
      $total = Advertisement::where('ad_position', $id)->count();
      return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => $data,
            'total'          => $total
          ], Response::HTTP_OK);
    }

    public function show_cartype_platform_ads(AdsCartypePlatfromApiRequest $request)
    {
      $cartype_id = $request->cartype_id;
      $platform   = $request->platform;

     $ad_positions    = Adpositions::get();
     $data = [];
     // $data['middle'];
     // $data['bottom'];
     foreach ($ad_positions as $ad_position) 
     {
          $ad_pos_name = $ad_position->position_name;
          $ddd = Advertisement::with(['car_type', 'adv_position'])
                                            ->where('cartype_id', $cartype_id)
                                            ->where('platform', $platform)
                                            ->where('ad_position', $ad_position->id)
                                            ->get();
          if ($ad_pos_name == 'carousel') {
            $data['carousel'] = $ddd;
          }
          if ($ad_pos_name == 'middle') {
            $data['middle'] = $ddd;
          }
          if ($ad_pos_name == 'bottom') {
            $data['bottom'] = $ddd;
          }
      }
      return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => $data,
           // 'total'         => $total
          ], Response::HTTP_OK);

     /* $data = Advertisement::with(['car_type', 'adv_position'])
                          ->where('cartype_id', $cartype_id)
                          ->where('platform', $platform)
                          ->get();
      $total = Advertisement::with(['car_type', 'adv_position'])
                          ->where('cartype_id', $cartype_id)
                          ->where('platform', $platform)->count();
      return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => $data,
            'total'          => $total
          ], Response::HTTP_OK);*/
    }
}
