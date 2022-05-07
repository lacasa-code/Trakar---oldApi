<!-- wp:html -->
<body style="
    background-color: #f6f6f6;
">
<div class="gmail_quote">
<div style="margin: 0!important; padding: 0!important;">
<table style="width: 100%;" border="0" cellspacing="0" cellpadding="0" bgcolor="#F6F6F6">
<tbody>
<tr>
<td style="text-align: center; padding: 4px;">

<p><span style="font-family: arial, helvetica, sans-serif;"><img class="wp-image-100 alignright" style="allign: center;padding: 15px;" src="https://traker.fra1.digitaloceanspaces.com/images/trkar_logo_white.png" alt="" width="190" height="77" /></span></p>


<table style="display: flex; justify-content: center;" border="0" cellspacing="0" cellpadding="0" bgcolor="#F6F6F6">
<tbody>
<tr>
<td style="text-align: right; border: 30px solid #ffffff;" align="center" bgcolor="#FFFFFF">
<div><span style="font-size: 18pt;"><strong><span class=" author-d-1gg9uz65z1iz85zgdz68zmqkz84zo2qowz82z5hz69zz73zpz65zsfgxtqwdz67zkz80zeyz78zyz86zz75zx4sz66zu50" style="font-family: arial, helvetica, sans-serif;">

</span></strong></span></div>
<div> </div>
<div><span class=" author-d-1gg9uz65z1iz85zgdz68zmqkz84zo2qowz82z5hz69zz73zpz65zsfgxtqwdz67zkz80zeyz78zyz86zz75zx4sz66zu50" style="font-family: arial, helvetica, sans-serif;">

 <!--   {{ $check_shipping->user->name }}, مرحبا    -->

 <br>

 تلقيتم  للتو طلبا جديدا بإنتظار الموافقة

<br>
 {{ $check_shipping->order_number }}  : رقم الطلب : 
<br>
<table style="height: 338px; width: 97.7092%; border-collapse: collapse; border-top: 2px solid #f6f6f6; min-width: 500px;" border="0">
<tbody style="direction: rtl;">
<tr style="height: 62px; border-bottom: 1px solid #f6f6f6; border-right: 3px solid #f6f6f6; border-left: 3px solid #f6f6f6;">
<td style="width: 18.6467%; height: 62px; text-align: center;">سعر</td>
<td style="width: 17.2311%; height: 62px; text-align: center;">كمية</td>
<td style="width: 64.6792%; height: 62px; text-align: center;" colspan="2">منتج</td>
</tr>

@foreach($vendor_details as $detail)
  <tr style="height: 124px; border-bottom: 1px solid #f6f6f6; border-right: 3px solid #f6f6f6; border-left: 3px solid #f6f6f6;">
<td style="width: 18.6467%; height: 66px; text-align: center;"><span style="font-size: 8pt;">
{{ $detail->total }} </span></td>
<td style="width: 17.2311%; height: 66px; text-align: center;"><span style="font-size: 8pt;">
{{ $detail->quantity }} </span></td>
<td style="width: 44.7396%; height: 66px;"><span style="font-size: 10pt;"><span style="font-size: 8pt;">  
	{{ $detail->product->name }}
</span> </span></td>
<td style="width: 19.9396%;"><span style="font-size: 10pt;"><img class="wp-image-125 alignright" style="font-size: 13.3333px; letter-spacing: -0.315px; text-align: left; display: block; margin-left: auto; margin-right: auto;" src="{{$detail->product->photo[0]->image}}" alt="" width="33" height="33" /><br /></span></td>
</tr>
@endforeach

<tr style="height: 40px; border-right: 3px solid #f6f6f6; border-left: 3px solid #f6f6f6;">
<td style="width: 100.557%; height: 10px; border-bottom: 3px solid #f6f6f6;" colspan="4">
<p style="text-align: left;">&nbsp;</p>

<!-- <p style="text-align: left;"><span style="font-size: 8pt;">رسوم الشحن: 115&nbsp; &nbsp; &nbsp;&nbsp;</span></p> -->
<p style="text-align: left;"><span style="font-size: 8pt;">&nbsp;

 الإجمالى  : {{ $vendor_total }}  &nbsp; &nbsp; &nbsp;&nbsp;</span></p> 

<p style="text-align: left;"><span style="font-size: 8pt;">&nbsp; &nbsp; &nbsp;&nbsp;</span></p>
</td>
</tr>
<tr style="height: 10px;">
<td style="width: 100.557%; height: 10px; border-bottom: 1px solid #f6f6f6;" colspan="4"><span style="font-size: 8pt;">&nbsp;</span><br /><span style="font-size: 8pt;">فى حال تاكيد  الطلب سيتم الشحن الى العنوان</span><br /><span style="font-size: 8pt;"><br /></span></td>
</tr>
<tr style="height: 90px;">
<td style="width: 100.557%; height: 90px; border-bottom: 1px solid #f6f6f6;" colspan="4">
<p><span style="font-size: 8pt;">&nbsp;</span><br />
	<span style="font-size: 8pt;"> {{ $exist_default->recipient_name }}  </span></p>
<p><span style="font-size: 8pt;">  {{ $exist_default->district }}   </span></p>
<p><span style="font-size: 8pt;">  {{ $exist_default->street }}      </span></p>
<p><span style="font-size: 8pt;">  {{ $exist_default->country->country_name }} </span></p>
<p><span style="font-size: 8pt;">  {{ $exist_default->recipient_phone }} </span><br /><span style="font-size: 8pt;"><br /></span></p>
</td>
</tr>

<tr style="height: 90px;">
<td style="width: 100.557%; height: 90px;" colspan="4">
<p><span style="font-size: 8pt;">&nbsp;</span><br /><span style="font-size: 8pt;">
</span></p>
<!-- <p><span style="font-size: 8pt;">وشكرا.</span><br /><span style="font-size: 8pt;"><br /></span></p> -->
</td>
</tr>
</tbody>
</table>
 <br>
<link>

</span></div>
<!-- <p><span style="font-family: arial, helvetica, sans-serif; color: #ff9900; font-size: 14pt;"><a style="color: #ff9900;" href="https://dashboard.lacasacode.dev/sign-in"><span class=" author-d-1gg9uz65z1iz85zgdz68zmqkz84zo2qowz82z5hz69zz73zpz65zsfgxtqwdz67zkz80zeyz78zyz86zz75zx4sz66zu50"> هذا الرابط</span></a></span></p> -->

<div><span class=" author-d-1gg9uz65z1iz85zgdz68zmqkz84zo2qowz82z5hz69zz73zpz65zsfgxtqwdz67zkz80zeyz78zyz86zz75zx4sz66zu50" style="font-family: arial, helvetica, sans-serif;">.وشكرا</span></div>
<p style="text-align: left;"><strong><span style="font-family: arial, helvetica, sans-serif; font-size: 18pt;"></span></strong></p>
<p style="text-align: left;"><span style="font-family: arial, helvetica, sans-serif;">


<!-- Hello, {{ $check_shipping->user->name  }} -->

Thank you for using Trkar.<br>
Your order has been successfully received: <br>
Order Number : {{ $check_shipping->order_number }}, 

<br>
<table style="height: 338px; width: 97.7092%; border-collapse: collapse; border-top: 2px solid #f6f6f6; min-width: 500px;" border="0">
<tbody style="direction: rtl;">
<tr style="height: 62px; border-bottom: 1px solid #f6f6f6; border-right: 3px solid #f6f6f6; border-left: 3px solid #f6f6f6;">
<td style="width: 18.6467%; height: 62px; text-align: center;"> Sub Total   </td>
<td style="width: 17.2311%; height: 62px; text-align: center;"> Quantity  </td>
<td style="width: 64.6792%; height: 62px; text-align: center;" colspan="2"> Product  </td>
</tr>

@foreach($vendor_details as $detail)
  <tr style="height: 124px; border-bottom: 1px solid #f6f6f6; border-right: 3px solid #f6f6f6; border-left: 3px solid #f6f6f6;">
<td style="width: 18.6467%; height: 66px; text-align: center;"><span style="font-size: 8pt;">
{{ $detail->total }} </span></td>
<td style="width: 17.2311%; height: 66px; text-align: center;"><span style="font-size: 8pt;">
{{ $detail->quantity }} </span></td>
<td style="width: 44.7396%; height: 66px;"><span style="font-size: 10pt;"><span style="font-size: 8pt;">  

	{{ $detail->product->name }}
</span> </span></td>
<td style="width: 19.9396%;"><span style="font-size: 10pt;"><img class="wp-image-125 alignright" style="font-size: 13.3333px; letter-spacing: -0.315px; text-align: left; display: block; margin-left: auto; margin-right: auto;" src="{{$detail->product->photo[0]->image}}" alt="" width="33" height="33" /><br /></span></td>
</tr>
@endforeach

<tr style="height: 40px; border-right: 3px solid #f6f6f6; border-left: 3px solid #f6f6f6;">
<td style="width: 100.557%; height: 10px; border-bottom: 3px solid #f6f6f6;" colspan="4">
<p style="text-align: left;">&nbsp;</p>

<!-- <p style="text-align: left;"><span style="font-size: 8pt;">رسوم الشحن: 115&nbsp; &nbsp; &nbsp;&nbsp;</span></p>  -->
<p style="text-align: left;"><span style="font-size: 8pt;">&nbsp;

 Total : {{ $vendor_total }}    &nbsp; &nbsp; &nbsp;&nbsp;</span></p> 

<p style="text-align: left;"><span style="font-size: 8pt;">&nbsp; &nbsp; &nbsp;&nbsp;</span></p>
</td>
</tr>
<tr style="height: 10px;">
<td style="width: 100.557%; height: 10px; border-bottom: 1px solid #f6f6f6;" colspan="4"><span style="font-size: 8pt;">&nbsp;</span><br /><span style="font-size: 8pt;">ف

In case Order Got Approved , it is gonna be shipped to the following address down here:

</span><br /><span style="font-size: 8pt;"><br /></span></td>
</tr>
<tr style="height: 90px;">
<td style="width: 100.557%; height: 90px; border-bottom: 1px solid #f6f6f6;" colspan="4">
<p><span style="font-size: 8pt;">&nbsp;</span><br />
	<span style="font-size: 8pt;"> {{ $exist_default->recipient_name }}  </span></p>
<p><span style="font-size: 8pt;">  {{ $exist_default->district }}   </span></p>
<p><span style="font-size: 8pt;">  {{ $exist_default->street }}      </span></p>
<p><span style="font-size: 8pt;">  {{ $exist_default->country->country_name }} </span></p>
<p><span style="font-size: 8pt;">  {{ $exist_default->recipient_phone }} </span><br /><span style="font-size: 8pt;"><br /></span></p>
</td>
</tr>

<tr style="height: 90px;">
<td style="width: 100.557%; height: 90px;" colspan="4">
<p><span style="font-size: 8pt;">&nbsp;</span><br /><span style="font-size: 8pt;">

 </span></p>
<p><span style="font-size: 8pt;">  </span><br /><span style="font-size: 8pt;"></span></p>
</td>
</tr>
</tbody>
</table>
</span></p>
<!-- <p style="text-align: left;"><span style="font-family: arial, helvetica, sans-serif; color: #ff9900; font-size: 14pt;"> <a style="color: #ff9900;" href="https://dashboard.lacasacode.dev/sign-in">Through this link.</a></span></p> -->

<p style="text-align: left;">Thanks .</p>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
<tr>
<td style="text-align: center;padding: 30px;">
<p><img class="wp-image-100 aligncenter" src="https://traker.fra1.digitaloceanspaces.com/images/trkar_logo_white.png" alt="" width="166" height="44" style="
    padding-bottom: 15px;
"></p>
<p><a style="text-decoration: none;" href="https://www.instagram.com/"> <img class="alignnone  wp-image-117" src="https://traker.fra1.digitaloceanspaces.com/images/Instagram.png" alt="" width="25" height="25" />  </a><a style="text-decoration: none;" href="https://www.twitter.com/"> <img class="alignnone  wp-image-119" src="https://traker.fra1.digitaloceanspaces.com/images/Twitter.png" alt="" width="25" height="25" />  </a><a style="text-decoration: none;" href="https://www.facebook.com/"> 


	 <img class="aligncenter wp-image-118 " src="https://traker.fra1.digitaloceanspaces.com/images/Facebook.png" alt="" width="25" height="25" /></a></p>
<p>

	<span style="font-size: 14pt;">

		<a style="text-decoration: none;" href="https://frontend.lacasacode.dev/info/about" target="_blank" rel="noopener noreferrer"> 
			من نحن  
		</a>

		<a style="text-decoration: none;" href="https://frontend.lacasacode.dev/info/FAQs" target="_blank" rel="noopener noreferrer"> 
			الأسئلة الشائعة   
		</a>

		<a style="text-decoration: none;" href="https://frontend.lacasacode.dev/sell/how-to" target="_blank" rel="noopener noreferrer"> 
			البيع على تركار   
		</a>                

    </span>

</p>

<p>
	<span style="font-size: 18pt;">
		<a style="text-decoration: none;" href="https://frontend.lacasacode.dev/" target="_blank" rel="noopener noreferrer"> 
			
			<img class="" style="padding-right: 9px;" src="https://traker.fra1.digitaloceanspaces.com/images/appstore-download.png" alt="Mobile Forms on App Store" width="156" height="44" />
		</a>

		<a style="text-decoration: none;" href="https://play.google.com/store/apps/details?id=com.lacasa.trkar" target="_blank" rel="noopener noreferrer">
			<img class="" src="https://traker.fra1.digitaloceanspaces.com/images/googleplay-get.png" alt="Mobile Forms on Play Store" width="156" height="47" />
		</a>
	</span>
</p>

<p><img class="alignnone size-full wp-image-122" src="https://traker.fra1.digitaloceanspaces.com/images/ic-contact-mail.png" alt="" /> info@trkar.com  <img class="alignnone size-full wp-image-121" style="padding-left: 11px;" src="https://traker.fra1.digitaloceanspaces.com/images/ic-contact-phone.png" alt="" />+9605000000</p>
<p>جميع الحقوق محفوظة ©2021 تركار</p>
</td>
</tr>
</tbody>
</table>
</div>
</div>