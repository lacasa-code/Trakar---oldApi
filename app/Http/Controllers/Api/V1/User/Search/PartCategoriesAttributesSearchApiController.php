<?php

namespace App\Http\Controllers\Api\V1\User\Search;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Gate;
use App\Http\Requests\SearchApisRequest;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Schema;
use App\Http\Resources\User\Search\ProductSearchApiResource;
use Auth;
use App\Models\AddVendor;
use App\Http\Requests\HomePageApiRequest;
use App\Models\PartCategory;
use App\Models\Allcategory;

class PartCategoriesAttributesSearchApiController extends Controller
{
    public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }

     public function part_categories_attributes(Request $request, $id)
     {
                $lang = $this->getLang();
               // $parts  = PartCategory::where('lang', $lang)->where('category_id', $id)->get();
                $parts  = Allcategory::where('allcategory_id', $id)->get();

            $data['width']   = array();
            $data['height']  = array();
            $data['size']    = array();
	        foreach ($parts as $value) 
	        {
	          $one = explode('/', $value->name);
	         // return strstr($one[2], '-', true);
	         
	         
           if (in_array($one[0], $data['width'])) {
             continue;
           }else{
            array_push($data['width'], $one[0]); //strstr($one[2], '-', true);
           }

           if (in_array($one[1], $data['height'])) {
             continue;
           }else{
            array_push($data['height'], $one[1]);
           }

           if (in_array($one[2], $data['size'])) {
             continue;
           }else{
             array_push($data['size'], $one[2]);
           }

           asort($data['size']);
           sort($data['width']);
           sort($data['height']);
	         
	       /* $data['width']   = $one[0]; //strstr($one[2], '-', true);
	        $data['height']  = $one[1];
	        $data['size']    = $one[2];*/
	         
	        }
	      //  $data = $parts;
	      //  $total = count($parts);
			        return response()->json([
			          'status_code'     => 200,
			          'message'         => 'success',
			          'data' => $data,
			      //    'total' => $total,
			        ], Response::HTTP_OK);
    } // end search products with name

    public function part_categories_attributes_v2(Request $request, $id)
     {
                $lang = $this->getLang();

          $widths = Product::where('approved', 1)->where('allcategory_id', $id)->groupBy('width')->pluck('width')->toArray();
          $heights= Product::where('approved', 1)->where('allcategory_id', $id)->groupBy('height')->pluck('height')->toArray();
          $sizes  = Product::where('approved', 1)->where('allcategory_id', $id)->groupBy('size')->pluck('size')->toArray();
           
            sort($sizes);
            sort($heights);
            sort($widths);
            $data['width']   = $widths;
            $data['height']  = $heights;
            $data['size']    = $sizes;

              return response()->json([
                'status_code'     => 200,
                'message'         => 'success',
                'data' => $data,
            //    'total' => $total,
              ], Response::HTTP_OK);

    } // end search products with name
}
