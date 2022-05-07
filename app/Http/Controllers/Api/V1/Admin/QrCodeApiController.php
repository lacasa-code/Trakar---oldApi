<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use QrCode;
use Illuminate\Support\Facades\Storage;
use DNS1D;
use DNS2D;

class QrCodeApiController extends Controller
{

	public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }


    public function generate()
    {
    	$name = 'ahmed';
    	echo DNS2D::getBarcodeHTML('vvv', 'QRCODE');

    	 $qr   = base64_encode(QrCode::size(300)->generate($name));
    	//return $qr   = array(QrCode::size(300)->generate($name))->render();
    	$image = \QrCode::format('png')
                     // ->merge('img/t.jpg', 0.1, true)
                     ->size(200)->errorCorrection('H')
                     ->generate('A simple example of QR code!');

        $output_file = '/img/qr-code/img-' . time() . '.png';
       // Storage::disk('local')->put($output_file, $image); 
        //storage/app/public/img/qr-code/img-1557309130.png
      //  return response($image)->header('Content-type','image/png');

    }
}
