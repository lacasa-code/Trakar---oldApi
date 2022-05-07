<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticketcategory;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Ticketpriority;

class TicketCategoryListApiController extends Controller
{
    public function getLang()
    {
      return $lang = \Config::get('app.locale');
    }

     // start list all
     public function list_all()
     {
        $lang = $this->getLang();
        $data = Ticketcategory::get();
       // $data = Ticketcategory::where('lang', $lang)->get();
        return response()->json(['data' => $data], Response::HTTP_OK);
     }
     // end list all 

     // start list all
     public function list_priority()
     {
        $lang = $this->getLang();
        //$data = Ticketpriority::where('lang', $lang)->get();
        $data = Ticketpriority::get();
        return response()->json(['data' => $data], Response::HTTP_OK);
     }
     // end list all 
}
