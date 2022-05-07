<?php 

// arabic file translation

return [
	'enter_email'           => 'enter email address',
	'unique_email'          => 'this email has already been taken',
	'email_format'          => 'enter valid email format',
	'vendor_email_email'    => 'enter valid email format',

	'vendor_name_string'    => 'vendor name should contain characters',
    'vendor_name_required'  => 'enter vendor name',
    'vendor_name_min'       => 'vendor name should be at least 3 characters',
    'vendor_name_unique'    => 'this name has already been taken',

    'enter_type'            => 'select type',
    'enter_type_options'    =>  'wrong type options',

    'enter_userid_id'       => 'اختر  الاكونت المناسب',
    'userid_id_integer'     => 'user id should be integer',
    'userid_id_exists'      => 'this user does not exist',

    'enter_image'           =>  'select image',
	'image_file'            =>  'image should be avalid file',
	'image_image'           =>  'select valid image',
	'image_mimes'           =>  'select valid image format',

	'category_name_required'  => 'enter category name',
	'category_name_string'    => 'category name should contain characters',
	'category_name_unique'    => 'this category name has already been taken',

	'year_integer'            => 'year should be a number format',
	'year_min'                => 'minimum year number is 1990',
	'year_max'                => 'year number should not exceed next year',
	'year_required'           => 'enter year field',
	'year_unique'             => 'this year number has already been taken',

	'carmade_required'        => 'select car made',
	'carmade_integer'         => 'car made should be a number',
	'carmade_exists'          => 'car made does not exist', 

	'carmodel_string'         => 'car model should contain characters',
	'carmodel_required'       => 'enter car model',
	'carmodel_unique'         => 'this car model has already been taken',

	'categoryid_id_required'  => 'select category',
	'categoryid_id_integer'   => 'select should be a number',
	'categoryid_id_exists'    => 'this category does not exist', 

	'car_made_string'         => 'car made should contain characters',
	'car_made_required'       => 'enter car made',
	'car_made_unique'         => 'this car made has already been taken',

	'category_name_string'    => 'category name should contain characters',
	'category_name_required'  => 'enter category name',
	'category_name_unique'    => 'this category name has already been taken',

	'store_address_required'              => 'enter store address',
	'store_address_string'                => 'store address should contain characters',

	'store_lat_required'                  =>  'enter latitude field',
	'store_lat_regex'                     =>  'latitude coordinates should be valid',

	'store_long_required'                 =>  'enter longitude field',
	'store_long_regex'                    =>  'longitude coordinates should be valid',

	'store_moderator_name_required'       => 'enter moderator name',
	'store_moderator_name_string'         => 'moderator name should contain characters',

	'store_moderator_phone_required'      => 'enter moderator phone',
	'store_moderator_phone_regex'         => 'moderator phone should be valid saudi number',

	// 'store_moderator_alt_phone_nullable'  => 'enter  alt phone',
	'store_moderator_alt_phone_regex'     => 'moderator alt phone should be valid saudi number',

	'store_name_required'   => 'enter store name',
	'store_name_string'     => 'store name should contain characters',
	'store_name_unique'     => 'this store name has already been taken',

	'title_string'          => 'title should contain characters',
	'title_required'        => 'enter title',
	'title_unique'          => 'this title has already been taken',

	'permissions_required'  => 'select permissions assigned',

	'tag_string'    => 'tag name should contain characters',
	'tag_required'  => 'enter tag name',
	'tag_unique'    => 'this tag has already been taken',

	// users section
	'password_required'   => 'يرجي  إدخال كلمة المرور',
    'roles_required'      => 'select roles assigned',

    'user_name_string'    => 'user name should contain characters',
	'user_name_required'  => 'enter user name',
	'user_name_unique'    => 'this user name has already been taken',

	'user_email_required' => 'enter email',
	'user_email_email'    => 'enter valid email format',
	'user_email_unique'   => 'this email has alread been taken',

	'product_categories_required'   => 'select categories related',
    'product_tags_required'         => 'select tags related',

	'product_car_made_id_required'  =>  'select car made',
	'product_car_made_id_integer'   =>  'car made should be a number',
	'product_car_made_id_exists'    =>  'this car made does not exist',

	'product_car_model_id_required' =>  'select car model',
	'product_car_model_id_required' =>  'car model should be a number',
	'product_car_model_id_required' =>  'this car model does not exist',

	'product_car_year_id_required'  =>  'select car year',
	'product_car_year_id_integer'   =>  'car year should be a number',
    'product_car_year_id_required'  =>  'this car year does not exist',

    'product_part_category_id_required'  => 'select part category',
	'product_part_category_id_integer'   => 'part category should be a number',
	'product_part_category_id_exists'    => 'this part category does not exist',

	'product_description_string'   => 'description should contain characters',
	'product_description_required' => 'enter description',
	'product_description_min'      => 'minimum description length is 5',

	'product_price_required'       => 'enter price',
	'product_price_numeric'        => 'price should be a number',
	'product_price_min'            => 'minimum price is 1',

	'product_discount_numeric'        => 'discount should be a number',
	'product_discount_min'            => 'minimum discount is 5 %',
	'product_discount_max'            => 'max discount is 85 %',

	'product_serial_number_required'  => 'enter serial number',
	'product_serial_number_unique'    => 'this serial number has already been taken',

	'product_store_id_required'       => 'select store',
	'product_store_id_integer'        => 'store should be a number',
	'product_store_id_exists'         => 'this store does not exist',

	'product_quantity_required'       => 'enter quantity',
	'product_quantity_integer'        => 'quantity should be a number', 
	'product_quantity_min'            => 'quantity should be at least 1', 

	'product_name_string'    => 'name should contain characters', 
	'product_name_required'  => 'enter product name',
	'product_name_unique'    => 'this product name has already been taken',

	'permission_name_string'    => 'name should contain characters', 
	'permission_name_required'  => 'enter permission name',
	'permission_name_unique'    => 'this permission name has already been taken',

	'question_required'         => 'enter question',
    'answer_required'           => 'enter answer',

    'login_email_email'         => 'enter valid email format',
	'login_email_required'      => 'يرجي إدخال البريد الإلكتروني',
	'login_email_regex'         => 'enter valid email format',
	'login_password_required'   => 'يرجي إدخال كلمة المرور',


	'prod_image_file'        => 'يرجي ادخال صورة صالحة ',
    'prod_image_file'        =>  'يرجي ادخال صورة صالحة ',
    'prod_image_mimes'       => 'يرجي ادخال صيغة صورة صالحة ',
    'prod_image_max'         => 'الحد الاقصس للصورة 1 ميجا ',

    'phone_no_required'      => 'يرجي ادخال رقم الجوال ',
    'phone_no_min'           => 'يجب أن يكون رقم الهاتف من 10 خانات على الأقل',
    'phone_no_max'           => 'لا يجب أن يتعدى رقم الهاتف 17 خانة',
    
];