<?php

namespace App\Http\Controllers\Api\V1\User\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ProductGetItsReviewsApiResource extends Controller
{
   // return parent::toArray($request);
        return[
            'name'                 => $this->name,
            'body_review' => $this->body_review,
	        'lang' => $this->lang,
	    	'user_id' =>$this->user_id,
	    	'product_id' => $this->product_id,
	    	'user_id' =>$this->user->name,
	    	'product_id' => $this->product->name,
            'time_created'     => Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)
                                        ->format('Y-m-d H:i:s'),
        ];
    }
}
