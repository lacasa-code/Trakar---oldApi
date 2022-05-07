<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Http;

class TokenApiController extends Controller
{
    public function fetch_data(Request $request)
    {
    	  if ($request->header('Authorization'))
	      {
	            $user = Auth::user();
	            if (!empty($user)) {
	              $user['roles'] = $user->roles->load('permissions');
	              $data['status'] = 'ON';
	              $data['user']   = $user;
	                    return response()->json(['data' => $data]);
	            }else{
	              $data['status'] = 'OFF';
	              return response()->json(['data' => $data]);
	            }
	        }
	        else
	        {
	          return response()->json(['errors' => 'No Authorization token'], 400);
	        }
    }

    public function token_refresh(Request $request)
    {

		$response = Http::asForm()->post('http://passport-app.com/oauth/token', [
		    'grant_type'    => 'refresh_token',
		    'refresh_token' => $request->refresh_token,
		    'client_id'     => 'client-id',
		    'client_secret' => 'client-secret',
		    'scope'         => '',
		]);

         return response()->json($response);
    }   
}
