<?php

namespace App\Http\Controllers\Api\V1\User\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Gate;
use Symfony\Component\HttpFoundation\Response;
use Auth;
use App\Models\Manufacturer;
use App\Http\Requests\SearchApisRequest;
use App\Models\Prodcountry;
use Carbon\Carbon;

class FetchOriginsManufacturersApiListController extends Controller
{
    public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }

    // start list all
     public function fetch_home_origins()
     {
        $lang = $this->getLang();
        // $data = Prodcountry::where('lang', $lang)->get();
        $data = Prodcountry::get();
        return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data' => $data], Response::HTTP_OK);
     }
     // end list all 

     // start list all
     public function fetch_home_manufacturers()
     {
        $lang = $this->getLang();
       // $data = Manufacturer::where('lang', $lang)->get();
        $data = Manufacturer::get();
        return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data' => $data], Response::HTTP_OK);
     }
     // end list all 
}
