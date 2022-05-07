<?php

namespace App\Http\Controllers\Api\V1\Admin\Ads;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Advertisement;
use App\Models\Adpositions;
use Auth;
use Gate;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Http\Requests\Website\User\Ads\AddAdvertisementApiRequest;
use App\Http\Requests\Website\User\Ads\UpdateAdvertisementApiRequest;
use Illuminate\Support\Facades\Storage;

class AdApiController extends Controller
{
   public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }

    public function index(Request $request)
    {
      abort_if(Gate::denies('advertisements_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
      // $lang = $this->getLang();
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
      $PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
        
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
      abort_if(Gate::denies('advertisements_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');
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
      abort_if(Gate::denies('advertisements_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
      $data = Advertisement::with(['car_type', 'adv_position'])->where('ad_position', $id)->get();
      $total = Advertisement::where('ad_position', $id)->count();
      return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => $data,
            'total'          => $total
          ], Response::HTTP_OK);
    }

    public function store(AddAdvertisementApiRequest $request)
    {
    	$advertisement = Advertisement::create($request->all());
      // file data
        /* new */
        $image = $request->file('photo');
        $imageFileName = time() . '.' . $image->getClientOriginalExtension();
        $path = Storage::disk('spaces')->putFile('advertisement-images', $image);
        Storage::disk('spaces')->setVisibility($path, 'public');
        $url   = Storage::disk('spaces')->url($path);
       // return $url;
        $advertisement->addMediaFromUrl($url)
                       ->toMediaCollection('photo');
        /* new */
    	return response()->json([
            'status_code'   => 201,
            'message'       => 'success',
            'data'          => $advertisement,
          ], Response::HTTP_CREATED);
    }

    public function update(UpdateAdvertisementApiRequest $request, $id)
    {
    	$advertisement = Advertisement::findOrFail($id);
    	$advertisement->update($request->all());

      // change media only on change of input request 
        if ($request->has('photo') && $request->photo != '') {
            if (!$advertisement->photo || $request->file('photo') !== $advertisement->photo->file_name) {
                if ($advertisement->photo) {
                    $advertisement->photo->delete();
                }
                    /* new */
                  $image = $request->file('photo');
                  $path = Storage::disk('spaces')->putFile('advertisement-images', $image);
                  Storage::disk('spaces')->setVisibility($path, 'public');
                  $url   = Storage::disk('spaces')->url($path);
                  $advertisement->addMediaFromUrl($url)
                                     ->toMediaCollection('photo');
                    /* new */
            }
        } 
    	return response()->json([
            'status_code'   => 202,
            'message'       => 'success',
            'data'          => $advertisement
          ], Response::HTTP_ACCEPTED);
    }

    public function destroy($id)
    {
      abort_if(Gate::denies('advertisements_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');
       $advertisement = Advertisement::findOrFail($id);
       $advertisement->delete();
       return response()->json([
            'status_code'   => 200,
            'message'       => 'success',
            'data'          => null
          ], 200);	
    }
}
