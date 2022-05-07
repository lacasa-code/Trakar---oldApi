<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transmission;
use Symfony\Component\HttpFoundation\Response;

class TransmissionApiController extends Controller
{
  public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }

     // start list all
     public function list_all()
     {
        $lang = $this->getLang();
        $data = Transmission::all();
       // $data = Transmission::where('lang', $lang)->get();
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
          'data' => $data], Response::HTTP_OK);
     }
     // end list all 
}
