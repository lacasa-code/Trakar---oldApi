<?php

namespace App\Http\Controllers\Api\V1\User\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Gate;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\Orderdetail;
use App\Models\Invoice;
use Validator;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Wishlist;
use App\Models\Userpaymentway;
use App\Models\Paymentway;
use App\Http\Requests\Website\User\Profile\EditProfileApiRequest;
use App\Http\Requests\Website\User\Profile\SiteChangePasswordApiRequest;
use Hash;
use App\Http\Requests\ResetPasswordRequest;
use Illuminate\Support\Facades\Password;
use App\Apicode;
use Mail;
use App\Mail\ResetPasswordMail;
use Session;
use DB;
use App\Models\AddVendor;
use App\Models\Store;

class UserProfileApiController extends Controller
{
    public function user_profile_info()
    {
      // abort_if(Gate::denies('show_orders_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
      $user = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();

     // if (in_array('User', $user_roles)) {
      	$total = Order::where('user_id', $user->id)->where('approved', 1)
      	                                ->count();
      	$last_transaction_time = Order::where('user_id', $user->id)
      	                                ->orderBy('created_at', 'desc')->take(1)
      	                                ->first();
        if ($last_transaction_time) {
          $last_transaction_time = $last_transaction_time->created_at;
        }else{
          $last_transaction_time = 'no transactions yet';
        }

        $method       = Userpaymentway::where('user_id', $user->id)->first();
        if ($method) {
          $user_payment = Paymentway::where('id', $method->paymentway_id)->first()
                                  ->payment_name;
        }else{
          $user_payment = 'default way not selected yet';
        }
        
      	$data = [
      		'name'            => $user->name,
          'email'           => $user->email,
          'roles'         => $user->roles,
          'email_verified_at' => $user->email_verified_at,
          'last_name'        => $user->last_name,
          'phone_no'     => $user->phone_no == null ? null : $user->phone_no,
          'birthdate'     => $user->birthdate,
          'gender'     => $user->gender,
      		'wishlists_count' => $user->wishlists()->count(),
      		'history_count'   => $total,
      		'user_shippings'  => $user->shippings,
      		'payment_method'  => $user_payment,
      		'last_transaction_time'  =>  $last_transaction_time,
          'last_name'      => $user->last_name,
          'phone_no'       => $user->phone_no,
          'birthdate'      => $user->birthdate,
          'gender'         => $user->gender,
      	];

        if (in_array('Vendor', $user_roles)) {
          $exist_vendor = AddVendor::where('userid_id', $user->id)->first();
          $data['vendor_details'] = $exist_vendor;
          $data['vendor_details']['completed_docs_status'] = $this->check_profile_complete($exist_vendor->id);
        }

        
            return response()->json([
                    'status_code' => 200, 
                    'message'     => 'success',
                    'data'  => $data,
                    //'total' => $total,
            ], 200);
      /*} 
      else{
        return response()->json([
                'status_code' => 401, 
               // 'message'     => 'success',
                'message'  => 'un authorized access page due to permissions',
               ], 401);
      }*/
    }

    public function check_profile_complete($vendor_id)
    {
        $vendor = AddVendor::findOrFail($vendor_id);
        $exist_center = Store::where('vendor_id', $vendor_id)->where('head_center', 1)->first();
    
          if ($vendor->type == 1)  // start normal vendor
          {
              if ($vendor->commercial_no != null && $vendor->tax_card_no != null && $vendor->bank_account != null && $vendor->taxCardDocs != null && $vendor->bankDocs != null && $vendor->commercialDocs != null ) 
              {
                  return 1; 
              }
              else{
                return 0;
              }
          }  // end normal vendor
          else
          {   // start wholesale or both vendor
            if ($vendor->commercial_no != null && $vendor->tax_card_no != null && $vendor->bank_account != null && $vendor->taxCardDocs != null && $vendor->commercialDocs != null && $vendor->bankDocs != null && $vendor->wholesaleDocs != null) 
             {
                 return 1; 
              }
              else{
                return 0;
              }
          }  // end wholesale or both vendor
    }

    public function account_summary(Request $request)
    {
      if (!$request->has('from') && !$request->has('to') || ($request->from == '' && $request->to == ''))
      {
       // $from = Carbon::today()->subMonth()->toDateString();
       $from = Carbon::today()->subDays(10)->toDateString();
       $to   = Carbon::today()->toDateString();

        $startDate = $from.' 00:00:00';
        $endDate   = $to.' 23:59:59';
      }
      else // case sent date filter (make validation)
      {
        $validator = Validator::make($request->all(), [
          'from' => 'required_with:to|date|date_format:Y-m-d|before_or_equal:to',
          'to'   => 'required_with:from|date|date_format:Y-m-d|after_or_equal:from',
        ]);
        if ($validator->fails()) {
          return response()->json([
            'status_code' => 400, 
            'message'     => 'fail',
            'errors' => $validator->errors()], 400);
        }

        $from = $request->from;
        $to   = $request->to;

        $startDate = $from.' 00:00:00';
        $endDate   = $to.' 23:59:59';
      }

      $PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      $user = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();

      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;

      if (in_array('User', $user_roles)) {

        $data = Order::where('user_id', $user->id)->where('approved', 1)
                                                        ->where('created_at', '>=', $startDate)
                                                        ->where('created_at', '<=', $endDate)
                                                        ->skip(($page-1)*$PAGINATION_COUNT)
                                                        ->take($PAGINATION_COUNT)
                                                        ->orderBy($ordered_by, $sort_type)
                                                        ->get();
        $total = Order::where('user_id', $user->id)->where('approved', 1)
                                                        ->where('created_at', '>=', $startDate)
                                                        ->where('created_at', '<=', $endDate)
                                                        ->count();
        
            return response()->json([
                    'status_code' => 200, 
                    'message'     => 'success',
                    'data'  => $data,
                    'total' => $total,
            ], 200);
      } 
      else{
        return response()->json([
                'status_code' => 401, 
                // 'message'     => 'success',
                'message'  => 'un authorized access page due to permissions',
               ], 401);
      }
    }

    public function edit_profile(EditProfileApiRequest $request)
    {
      $user       = Auth::user();
      $user_id    = Auth::user()->id;
      $user_roles = $user->roles->pluck('title')->toArray();

      $uniqueMail = User::where('email', $request->email)->where('id', '!=', $user_id)->first();
      $uniqueName = User::where('name', $request->name)->where('id', '!=', $user_id)->first();

     /* if ($uniqueName != null) {
        return response()->json([
                'status_code' => 400, 
                // 'message'     => 'success',
                'errors'  => 'this name has already been taken',
               ], 400);
      }*/

      if ($uniqueMail != null) {
        return response()->json([
                'status_code' => 400, 
                // 'message'     => 'success',
                'errors'  => 'this email has already been taken',
               ], 400);
      }

      $user->update($request->all());
      if (in_array('Vendor', $user_roles)) 
        {
            $vendor     = AddVendor::where('userid_id', Auth::user()->id)->first();
            $vendor->update([
              //'email'       => $user->email,
              'vendor_name' => $user->name,
              'email'       => $user->email,
            ]);
        }
      
      return response()->json([
                'status_code' => 200, 
               // 'message'     => 'profile data updated successfully',
                'message' => __('site_messages.Your_profile_updated'),
               // 'errors'  => 'this email has already been taken',
               ], 200);
    }

    public function change_password(SiteChangePasswordApiRequest $request)
    {
      $user       = Auth::user();
      $user_id    = Auth::user()->id;
      $user_roles = $user->roles->pluck('title')->toArray();

      $exist_user = User::where('email', $request->email)->first();
      if ($exist_user == null) {
          return response()->json([
                'status_code' => 400, 
                // 'message'     => 'success',
                'errors'  => 'wrong email',
               ], 400);
      }
      if ($exist_user->email != $user->email) {
        return response()->json([
                'status_code' => 400, 
                // 'message'     => 'success',
                'errors'  => 'email does not belong',
               ], 400);
      }

        $validator = Validator::make($request->all(), [
          'current_password' => ['required', 'string'],
          'new_password' => ['required','confirmed','min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*(_|[^\w])).+$/'],
        ]);
        if ($validator->fails()) {
          return response()->json([
            'status_code' => 400, 
            'message'     => 'fail',
            'errors' => $validator->errors()], 400);
        }
      
      if(Hash::check($request->current_password, $user->password)) 
      {
        $user->update(['password' => $request->new_password]);
          return response()->json([
            'status_code' => 200, 
            'message'     => 'password reset successfully',
            // 'errors' => $validator->errors()
          ], 200);
      }
      else
      {
          return response()->json([
            'status_code' => 400, 
            'errors'     => 'incorrect password',
            // 'errors' => $validator->errors()
          ], 400);
      }  
    }

    public function forgot(SiteChangePasswordApiRequest $request)
    {
      $credentials = $request->only('email');
      Password::sendResetLink($credentials);
      return $this->respondWithMessage('Reset password link sent on your email id.');
     // Mail::to($credentials)->send('token is 1234');
     // $data = ['subject' => 'reset password'];
     // Mail::to($credentials)->send(new ResetPasswordMail($data));
    }


    public function reset(ResetPasswordRequest $request) {
        $reset_password_status = Password::reset($request->validated(), function ($user, $password) {
            $user->password = $password;
            $user->save();
        });

        if ($reset_password_status == Password::INVALID_TOKEN) {
            return $this->respondBadRequest(Apicode::INVALID_RESET_PASSWORD_TOKEN);
        }

        return $this->respondWithMessage("Password has been successfully changed");
    }

public function forgot_password(Request $request)
{
    $input = $request->all();
    $rules = array(
        'email' => "required|email",
    );
    $validator = Validator::make($input, $rules);
    if ($validator->fails()) {
        $arr = array("status" => 400, "message" => $validator->errors()->first(), "data" => array());
    } else {
        try {
            $response = Password::sendResetLink($request->only('email'), function (Message $message) {
                $message->subject($this->getEmailSubject());
            });
            switch ($response) {
                case Password::RESET_LINK_SENT:
                    return \Response::json(array("status" => 200, "message" => trans($response), "data" => array()));
                case Password::INVALID_USER:
                    return \Response::json(array("status" => 400, "message" => trans($response), "data" => array()));
            }
        } catch (\Swift_TransportException $ex) {
            $arr = array("status" => 400, "message" => $ex->getMessage(), "data" => []);
        } catch (Exception $ex) {
            $arr = array("status" => 400, "message" => $ex->getMessage(), "data" => []);
        }
    }
    return \Response::json($arr);
}

// start logout
    public function logout()
    {
      $accessToken = Auth::user()->token();
        DB::table('oauth_refresh_tokens')
            ->where('access_token_id', $accessToken->id)
            ->update([
                'revoked' => true
            ]);

        $accessToken->revoke();
        return response()->json(['message' => 'successfully logged out'], 200);

        /*Auth::user()->tokens()->delete();
        Session::flush();
        return response()->json(['message' => 'successfully logged out'], Response::HTTP_OK);*/
    } 
    // end logout

}
