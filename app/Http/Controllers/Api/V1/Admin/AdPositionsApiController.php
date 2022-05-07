<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Adpositions;

class AdPositionsApiController extends Controller
{
    // start list all ads 

     public function list_all()
     {
        $data = Adpositions::all();
        return response()->json([
          'status_code'     => 200,
          'message'         => 'success',
          'data' => $data], 200);
     }
     
     // end list all ads
}
