<?php

namespace App\Http\Controllers\Api\V1\User\Authweb;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\ResetsPasswords;
use App\Http\Requests\UpdatePasswordApiRequest;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class ResetPasswordApiController extends Controller
{
  //  use ResetsPasswords;
      public function passwordResetProcess(UpdatePasswordApiRequest $request)
      {
        //return $this->updatePasswordRow($request)->count();
        return $this->updatePasswordRow($request)->count() > 0 ? $this->resetPassword($request) : $this->tokenNotFoundError();
      }
  
      // Verify if token is valid
      private function updatePasswordRow($request){
         return DB::table('password_resets')->where([
             //'email' => $request->email,
             'token' => $request->resetToken,
         ]);
      }
  
      // Token not found response  
      private function tokenNotFoundError() {
          return response()->json([
            'error' => 'Either your email or token is wrong.'
          ],Response::HTTP_UNPROCESSABLE_ENTITY);
      }
  
      // Reset password
      private function resetPassword($request) {
          // find email
        $oldToken = DB::table('password_resets')->where('token', $request->resetToken)->first();
        $userData = User::where('email', $oldToken->email)->first();
       // return $userData;;
          // update password
          $userData->update([
            'password'=> bcrypt($request->password)
          ]);
          // remove verification data from db
          $this->updatePasswordRow($request)->delete();
  
          // reset password response
          return response()->json([
            'data'=>'Password has been updated.'
          ],Response::HTTP_CREATED);
      }    
}