<?php

namespace App\Http\Controllers\Api\V1\User\Authweb;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Website\User\WebsiteRegisterUserApiRequest;
use App\Http\Requests\Website\User\AttachDocumentApiRequest;
use App\Models\User;
use Gate;
use Symfony\Component\HttpFoundation\Response;
// use Illuminate\Support\Facades\Schema;
use Auth;
use App\Http\Resources\Website\User\WebsiteRegisterUserApiResource;
use App\Http\Resources\Website\User\WebsiteUserRolesApiResource;
use DB;
use App\Models\AddVendor;
use Illuminate\Validation\Rule;
use Validator;
use App\Http\Resources\Website\User\WebsiteRegisterVendorApiResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Models\Role;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendRegisterMail;
use App\Models\Store;
use App\Mail\SendAdminVendorRequestMail;
use App\Mail\SendAdminReuploadMail;

class VendorUploadDoumentsApiController extends Controller
{
    use MediaUploadingTrait;

    public function getLang()
    {
      return $lang = \Config::get('app.locale');
    }
	
	public function vendor_upload_docs(Request $request)
	{
	    $v = Validator::make($request->all(), [
	    	    
	        'user_id'        => 'required|exists:users,id,deleted_at,NULL',
	        'vendor_id'      => 'required|exists:add_vendors,id,deleted_at,NULL',
	        'commercial_no'  => 'nullable|unique:add_vendors,commercial_no,'. $request->vendor_id,
	        'commercialDocs' => 'nullable|file|mimes:pdf,jpg,jpeg,png,gif,svg',
	        'tax_card_no'    => 'nullable|unique:add_vendors,tax_card_no,'. $request->vendor_id,
	        'taxCardDocs'    => 'nullable|file|mimes:pdf,jpg,jpeg,png,gif,svg',
	        'bank_account'   => 'nullable|unique:add_vendors,bank_account,'. $request->vendor_id,
          'bankDocs'       => 'nullable|file|mimes:pdf,jpg,jpeg,png,gif,svg',
	        'type'           => ['required', Rule::in('1','2', '3')],
          'company_name'   => 'required',
          // Rule::unique('add_vendors')->ignore($request->vendor_id)->whereNull('deleted_at')], 
	              ]);
      // 'required|unique:add_vendors,company_name,'. $request->vendor_id,

        if ($v->fails()) {
           return response()->json(['errors' => $v->errors()], 400);
        }

         if ($request->type == 2 || $request->type == 3)  // case wholesale or both
         {
              $add_field = Validator::make($request->all(), [
                    'wholesaleDocs' => 'nullable|file|mimes:pdf,jpg,jpeg,png,gif,svg',
                  ]);

                  if ($add_field->fails()) {
                    return response()->json([
                      'status_code' => 400,
                      'errors' => $add_field->errors()], 400);
                  }
        }

	    $user = User::findOrFail($request->user_id);
      $user_roles = $user->roles->pluck('title')->toArray();

	    if (!in_array('Vendor', $user_roles)) 
	    {
	    	return response()->json(['errors' => 'invalid user'], 400);
        } 

        $exist_vendor = AddVendor::where('id', $request->vendor_id)->first();
        if (!$exist_vendor) {
        	return response()->json(['errors' => 'invalid user'], 400);
        }
        $addVendor = AddVendor::where('userid_id', $request->user_id)->first();
        if ($exist_vendor->userid_id != $request->user_id) {
        	return response()->json(['errors' => 'invalid user'], 400);
        }

        if ($addVendor->complete == 1 && $addVendor->rejected != 1) {
        	return response()->json(['errors' => 'already completed profile'], 400);
        }

        if ($addVendor->complete == 1 && $addVendor->approved == 1) {
          return response()->json(['errors' => 'already completed approved profile'], 400);
        }

        if ($addVendor->rejected == 1) {
          $admin = User::findOrFail(1);
          $name = $addVendor->vendor_name;
          Mail::to($admin->email)->send(new SendAdminReuploadMail($name));
        }

        $id = \DB::table('add_vendors')->where('id', '!=', $request->vendor_id)
                        ->latest('created_at')->first();
    
              if ($id === NULL) {
                    if($request->type=='1')
                  {
                      $request['serial']='V001';
                  }elseif ($request->type=='2')
                  {
                      $request['serial']='H001';
                  }elseif ($request->type=='3')
                  {
                      $request['serial']='VH001';
                  }
              }
              else{
                      if($request->type=='1')
                  {
                      $request['serial']='V00'.($id->id + 1);
                  }elseif ($request->type=='2')
                  {
                      $request['serial']='H00'.($id->id + 1);
                  }elseif ($request->type=='3')
                  {
                      $request['serial']='VH00'.($id->id + 1);
                  }
              } // end else 

              $serial = $request['serial'];
              $addVendor->update($request->all());

               /* new commercialDocs */
              if ($request->has('commercialDocs') && $request->file('commercialDocs') != '') {
              	$commercial_image = $request->file('commercialDocs');
              	$path1 = Storage::disk('spaces')
                      ->putFile('vendor-documents/commercial-documents', $commercial_image);
                Storage::disk('spaces')->setVisibility($path1, 'public');
                $url1   = Storage::disk('spaces')->url($path1);
                $addVendor->addMediaFromUrl($url1)
                            ->toMediaCollection('commercialDocs');
              }
              else{
                  $reasons = $addVendor->rejectedreason->pluck('field')->toArray();
                  if (in_array('commercialDocs', $reasons)) {
                    $files = $addVendor->getMedia('commercialDocs')->pluck('id')->toArray();
                    $checkedMedia = Media::whereIn('id', $files)
                                ->where('model_id', $addVendor->id)->delete();
                    $request['commercialDocs'] = null;
                }/*else{
                    continue;
                  }*/
              }
              /* new commercialDocs */

          /* new taxCardDocs */
            if ($request->has('taxCardDocs') && $request->file('taxCardDocs') != '') {
              $tax_image = $request->file('taxCardDocs');
	            $path2 = Storage::disk('spaces')->putFile('vendor-documents/tax-documents',$tax_image);
	            Storage::disk('spaces')->setVisibility($path2, 'public');
	            $url2   = Storage::disk('spaces')->url($path2);  
	            $addVendor->addMediaFromUrl($url2)
	                       ->toMediaCollection('taxCardDocs');
              }
              else{
                  $reasons = $addVendor->rejectedreason->pluck('field')->toArray();
                  if (in_array('taxCardDocs', $reasons)) {
                      $files = $addVendor->getMedia('taxCardDocs')->pluck('id')->toArray();
                      $checkedMedia = Media::whereIn('id', $files)
                              ->where('model_id', $addVendor->id)->delete();
                      $request['taxCardDocs'] = null;
                  }/*else{
                    continue;
                  }*/
              }
          /* new taxCardDocs */


         if ($request->type == 1)  // case retail
         {
            if ($addVendor->getMedia('wholesaleDocs') != null)
            {
              $files = $addVendor->getMedia('wholesaleDocs')->pluck('id')->toArray();
              $checkedMedia = Media::whereIn('id', $files)
                              ->where('model_id', $addVendor->id)->delete();
              $request['wholesaleDocs'] = null;
            }
         }

         if ($request->type != 1)  // case retail
         {
              if ($request->has('wholesaleDocs') && $request->file('wholesaleDocs') != '') {
                $tax_image = $request->file('wholesaleDocs');
              $path2 = Storage::disk('spaces')->putFile('vendor-documents/wholesale-documents',$tax_image);
              Storage::disk('spaces')->setVisibility($path2, 'public');
              $url2   = Storage::disk('spaces')->url($path2);  
              $addVendor->addMediaFromUrl($url2)
                         ->toMediaCollection('wholesaleDocs');
              }
              else{
                  $reasons = $addVendor->rejectedreason->pluck('field')->toArray();
                  if (in_array('wholesaleDocs', $reasons)) {
                      $files = $addVendor->getMedia('wholesaleDocs')->pluck('id')->toArray();
                      $checkedMedia = Media::whereIn('id', $files)
                              ->where('model_id', $addVendor->id)->delete();
                      $request['wholesaleDocs'] = null;
                  }/*else{
                    continue;
                  }*/
              }
         }  // case retail
        /* bankdocs */ 
            if ($request->has('bankDocs') && $request->file('bankDocs') != '') {
              //return 'one';
                $tax_image = $request->file('bankDocs');
              $path2 = Storage::disk('spaces')->putFile('vendor-documents/bank-documents',$tax_image);
              Storage::disk('spaces')->setVisibility($path2, 'public');
              $url2   = Storage::disk('spaces')->url($path2);  
              $addVendor->addMediaFromUrl($url2)
                         ->toMediaCollection('bankDocs');
              }
              else{
                  $reasons = $addVendor->rejectedreason->pluck('field')->toArray();
                  if (in_array('bankDocs', $reasons)) {
                      $files = $addVendor->getMedia('bankDocs')->pluck('id')->toArray();
                      $checkedMedia = Media::whereIn('id', $files)
                              ->where('model_id', $addVendor->id)->delete();
                      $request['bankDocs'] = null;
                  }/*else{
                    continue;
                  }*/
              }
          /* bankdocs */ 

          $addVendor->rejectedreason()->detach();
          $addVendor->update(['rejected' => 0]);


            $this->check_profile_complete($addVendor->id);
            if ($addVendor->complete == 1) {
        	// send admin email
            $admin = User::findOrFail(1);
            Mail::to($admin->email)->send(new SendAdminVendorRequestMail($addVendor->vendor_name));
            }
              $data = $addVendor;

              return response()->json([
              'status_code' => 200,
              'message'     => 'succcess', //full register waiting approval',
              'data'        => $data,
            ], Response::HTTP_OK);
    }

    public function check_profile_complete($vendor_id)
    {
    	$vendor = AddVendor::findOrFail($vendor_id);
    	$exist_center = Store::where('vendor_id', $vendor_id)->where('head_center', 1)->first();

      if ($vendor->type == 1)  // start normal vendor
      {
          if ($exist_center != null && $vendor->commercial_no != null && $vendor->tax_card_no != null && $vendor->bank_account != null && $vendor->taxCardDocs != null && $vendor->bankDocs != null && $vendor->commercialDocs != null ) 
          {
              $vendor->update(['complete' => 1]);
          }else{
            $vendor->update(['complete' => 0]);
          }
      }  // end normal vendor
      else
      {   // start wholesale or both vendor
        if ($exist_center != null && $vendor->commercial_no != null && $vendor->tax_card_no != null && $vendor->bank_account != null && $vendor->taxCardDocs != null && $vendor->commercialDocs != null && $vendor->bankDocs != null && $vendor->wholesaleDocs != null) 
         {
             $vendor->update(['complete' => 1]);
         }else{
            $vendor->update(['complete' => 0]);
          }
      }  // end wholesale or both vendor
    }
}
