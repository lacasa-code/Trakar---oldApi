<?php

namespace App\Http\Controllers\Api\V1\Admin\Price;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Api\V1\Admin\Price\EditTypePriceApiRequest;
use App\Models\Productprice;
use App\Models\Product;
use Validator;

class ProductPriceApiController extends Controller
{
    public function edit_type_price(EditTypePriceApiRequest $request, $id)
    {
    	$item_price = Productprice::findOrFail($id);
    	$product    = Product::findOrFail($item_price->product_id);
    	
    	if ($product->producttype_id == 1) {
    		return $this->case_type_one($product, $item_price, $request);
    	}

    	if ($product->producttype_id == 2) {
    		return $this->case_type_two($product, $item_price, $request);
    	}

    	if ($product->producttype_id == 3) {
    		return $this->case_type_three($product, $item_price, $request);
    	}
    }

    public function case_type_three($product, $item_price, $request)
    {
    	// case normal 1
	    	if ($item_price->producttype_id == 1 && $request->producttype_id == 1) {
	    		// $product->update(['producttype_id' => $request->producttype_id]);
	    		$request['num_of_orders'] = null;
	    		$item_price->update($request->all());
	    		return response()->json([
                      'status_code' => 200,
                      'message' => 'data updated successfully',], 200);
	        }
	        if ($item_price->producttype_id == 2 && $request->producttype_id == 2) {
	    		// $product->update(['producttype_id' => $request->producttype_id]);
	    		$item_price->update($request->all());
	    		return response()->json([
                      'status_code' => 200,
                      'message' => 'data updated successfully',], 200);
	        }
	        if ($item_price->producttype_id == 1 && $request->producttype_id == 2) {
	    		return response()->json([
                      'status_code' => 400,
                      'errors' => 'it alrady has wholesale price',], 400);
	        }
	        if ($item_price->producttype_id == 2 && $request->producttype_id == 1) {
	    		return response()->json([
                      'status_code' => 400,
                      'errors' => 'it alrady has normal price',], 400);
	        }
	}

    public function case_type_one($product, $item_price, $request)
    {
    	// case normal 1
	    	if ($request->producttype_id == 1) {
	    		$product->update(['producttype_id' => $request->producttype_id]);
	    		$request['no_of_orders']      = null;
	    		$item_price->update($request->all());
	    		return response()->json([
                      'status_code' => 200,
                      'message' => 'data updated successfully',], 200);
	        }
	        if ($request->producttype_id == 2) {
	        	$holesale_price_v = Validator::make($request->all(), [
                    // 'holesale_price' => 'required|numeric|min:1',
                    'num_of_orders'   => 'required|integer|min:1',
                  ]);

                  if ($holesale_price_v->fails()) {
                    return response()->json([
                      'status_code' => 400,
                      'errors' => $holesale_price_v->errors()], 400);
                  }
                  
	        	  $exist = Productprice::withTrashed()->where('producttype_id', 2)
                                                    ->latest()->first();
                    if ($exist == null) {
                      $serial_coding = 'H01';
                    }else{
                      $serial_coding = 'H0'. ($exist->id + 1);
                    }
	        	$request['product_id']        = $item_price->product_id;
	        	$request['serial_coding_seq'] = $serial_coding;
	        	$request['no_of_orders']      = $request->no_of_orders;
	        	$product->update(['producttype_id' => $request->producttype_id]);
	    		$item_price->update($request->all());
	    		return response()->json([
                      'status_code' => 200,
                      'message' => 'data updated successfully',], 200);
	        }
	        if ($request->producttype_id == 3) {
	        	$holesale_price_v = Validator::make($request->all(), [
                    // 'normalPrice'    => 'required|numeric|min:1',
                    'wholesalePrice' => 'required|numeric|min:1',
                    'num_of_orders'  => 'required|integer|min:1',

                  ]);

                  if ($holesale_price_v->fails()) {
                    return response()->json([
                      'status_code' => 400,
                      'errors' => $holesale_price_v->errors()], 400);
                  }
                  
	        	  $exist = Productprice::withTrashed()->where('producttype_id', 1)
                                                    ->latest()->first();
                    if ($exist == null) {
                      $serial_coding1 = 'BN01';
                      $serial_coding2 = 'BH01';
                    }else{
                      $serial_coding1 = 'BN0'. ($exist->id + 1);
                      $serial_coding2 = 'BH0'. ($exist->id + 1);
                    }

	        	$product->update(['producttype_id' => $request->producttype_id]);
	    		$item_price->update([
	    			'product_id'        => $item_price->product_id,
			        'producttype_id'    => 1,
			        'num_of_orders'     => null,
			        'price'             => $request->price,
			        'serial_coding_seq' => $serial_coding1
	    		]);
	    		Productprice::create([
	    			'product_id'        => $item_price->product_id,
			        'producttype_id'    => 2,
			        'num_of_orders'     => $request->no_of_orders,
			        'price'             => $request->wholesalePrice,
			        'serial_coding_seq' => $serial_coding2
	    		]);
	    		return response()->json([
                      'status_code' => 200,
                      'message' => 'data updated successfully',], 200);
	        }
    }

    public function case_type_two($product, $item_price, $request)
    {
    	// case normal 1
	    	if ($request->producttype_id == 1) {
	    		$exist = Productprice::withTrashed()->where('producttype_id', 1)
                                                    ->latest()->first();
                    if ($exist == null) {
                      $serial_coding = 'N01';
                    }else{
                      $serial_coding = 'N0'. ($exist->id + 1);
                    }
	        	$request['product_id']        = $item_price->product_id;
	        	$request['serial_coding_seq'] = $serial_coding;
	        	$request['no_of_orders']      = null;
	        	$product->update(['producttype_id' => $request->producttype_id]);
	    		$item_price->update($request->all());
	    		return response()->json([
                      'status_code' => 200,
                      'message' => 'data updated successfully',], 200);
	        }
	        if ($request->producttype_id == 2) {
	        	$holesale_price_v = Validator::make($request->all(), [
                    'num_of_orders'   => 'required|integer|min:1',
                  ]);

                  if ($holesale_price_v->fails()) {
                    return response()->json([
                      'status_code' => 400,
                      'errors' => $holesale_price_v->errors()], 400);
                  }
                  
	        	$request['no_of_orders']      = $request->no_of_orders;
	        	$product->update(['producttype_id' => $request->producttype_id]);
	    		$item_price->update($request->all());
	    		return response()->json([
                      'status_code' => 200,
                      'message' => 'data updated successfully',], 200);
	        }
	        if ($request->producttype_id == 3) {
	        	//return 'vvv';
	        	$holesale_price_v = Validator::make($request->all(), [
                    'normalPrice'    => 'required|numeric|min:1',
                    // 'wholesalePrice' => 'required|numeric|min:1',
                    'num_of_orders'  => 'required|integer|min:1',

                  ]);

                  if ($holesale_price_v->fails()) {
                    return response()->json([
                      'status_code' => 400,
                      'errors' => $holesale_price_v->errors()], 400);
                  }
                  
	        	  $exist = Productprice::withTrashed()->where('producttype_id', 1)
                                                    ->latest()->first();
                    if ($exist == null) {
                      $serial_coding1 = 'BN01';
                      $serial_coding2 = 'BH01';
                    }else{
                      $serial_coding1 = 'BN0'. ($exist->id + 1);
                      $serial_coding2 = 'BH0'. ($exist->id + 1);
                    }

	    		$item_price->update([
	    			'product_id'        => $item_price->product_id,
			        'producttype_id'    => 2,
			        'num_of_orders'     => $request->num_of_orders,
			        'price'             => $request->price,
			        'serial_coding_seq' => $serial_coding2,
	    		]);
	    		Productprice::create([
	    			'product_id'        => $item_price->product_id,
			        'producttype_id'    => 1,
			        'num_of_orders'     => null,
			        'price'             => $request->normalPrice,
			        'serial_coding_seq' => $serial_coding1,
	    		]);
	    		$product->update(['producttype_id' => $request->producttype_id]);
	    		return response()->json([
                      'status_code' => 200,
                      'message' => 'data updated successfully',], 200);
	        }
    }
}
