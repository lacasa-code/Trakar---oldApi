<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use app\Models\Ticketcategory;

class TicketCategoryApiController extends Controller
{
	 // start list all
     public function list_all()
     {
        $data = Ticketcategory::all();
        return response()->json(['data' => $data], Response::HTTP_OK);
     }
     // end list all 
    
}
