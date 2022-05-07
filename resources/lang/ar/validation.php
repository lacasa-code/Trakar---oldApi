<?php

return [
    'accepted'                       => 'يجب قبول  :attribute.',
    'active_url'                     => 'هذا  :attribute  ليس رابط صالحا.',
    'after'                          => 'يجب أن يكون :attribute تاريخا بعد :date.',
    'after_or_equal'                 => 'يجب أن يكون :attribute تاريخا بعد أو يساوي :date.',
    'alpha'                          => 'قد يحتوي :attribute على أحرف فقط.',
    'alpha_dash'                     => 'قد تحتوي ال :attribute  على أحرف وأرقام وشرطات فقط.',
    'alpha_num'                      => 'قد تحتوي ال :attribute  على أحرف وأرقام  فقط.',
    'latin'                          => 'The :attribute may only contain ISO basic Latin alphabet letters.',
    'latin_dash_space'               => 'The :attribute may only contain ISO basic Latin alphabet letters, numbers, dashes, hyphens and spaces.',
    'array'                          => ':attribute  يجب ان تكون مصفوفة',
    'before'                         => 'يجب أن يكون :attribute تاريخا قبل :date.',
    'before_or_equal'                => 'يجب أن يكون :attribute تاريخا قبل أو يساوي :date.',
    'between'                        => [
        'numeric' => 'يجب أن يكون :attribute بين  :min و  :max  .',
        'file'    => 'يجب أن يكون :attribute بين  :min و  :max  كيلو بايت.',
        'string'  => 'يجب أن يكون :attribute بين  :min و  :max  حرف.',
        'array'   => 'يجب أن يكون :attribute بين  :min و  :max  نوع.',
    ],
    'boolean'                        => 'يجب أن يكون :attribute صح او خطأ',
    'confirmed'                      => 'تأكيد :attribute لا يطابق.',
    'date'                           => ':attribute  ليست تاريخا صالحا.',
    'date_equals'                    => ':attribute يجب ان تكون مساوية ل  :date.',
    'date_format'                    => ':attribute لايتطابق مع الصيغة :format.',
    'different'                      => 'يجب أن يختلف  :attribute عن :الاخرين.',
    'digits'                         => 'هذا :attribute يجب ان يكون :digits ارقام.',
    'digits_between'                 => 'هذايجب ان يكون بين :min و :max ارقام.',
    'dimensions'                     => 'هذه :attribute ذات ابعاد خاطئة.',
    'distinct'                       => 'هذا :attribute الحقل موجود مسبقا',
    'email'                          => 'هذا :attribute يجب ان يكون بريد الكتروني صالح',
    'ends_with'                      => ':attribute يجب أن ينتهي بواحد مما يلي: :قيم.',
    'exists'                         => 'الحقل المختار :attribute غير صالح',
    'file'                           => 'هذا :attribute يجب ان يكون ملف',
    'filled'                         => 'هذا :attribute الحقل مطلوب.',
    'gt'                             => [
        'numeric' => 'يجب أن يكون :attribute أكبر من :value  .',
        'file'    => 'يجب أن يكون :attribute أكبر من :value كيلوبايت.',
        'string'  => 'يجب أن يكون :attribute أكبر من :value  .',
        'array'   => 'يجب أن يحتوي :attribute على أكثر من: value عناصر .',
    ],
    'gte'                            => [
        'numeric' => 'The :attribute must be greater than or equal :value.',
        'file'    => 'The :attribute must be greater than or equal :value kilobytes.',
        'string'  => 'The :attribute must be greater than or equal :value characters.',
        'array'   => 'The :attribute must have :value items or more.',
    ],
    'image'                          => 'ال :attribute يجب ان تكون صورة',
    'in'                             => 'قيمة  :attribute المختارة غير صالحة.',
    'in_array'                       => 'حقل :attribute غير موجود فى :other.',
    'integer'                        => 'حقل :attribute يجب ان يكون رقم.',
    'ip'                             => ':attribute يجب ان يكون عنوان IP صالح.',
    'ipv4'                           => 'The :attribute must be a valid IPv4 address.',
    'ipv6'                           => 'The :attribute must be a valid IPv6 address.',
    'json'                           => ':attribute  يجب ان يكون في صيغة  JSON.',
    'lt'                             => [
        'numeric' => 'The :attribute must be less than :value.',
        'file'    => 'The :attribute must be less than :value kilobytes.',
        'string'  => 'The :attribute must be less than :value characters.',
        'array'   => 'The :attribute must have less than :value items.',
    ],
    'lte'                            => [
        'numeric' => 'The :attribute must be less than or equal :value.',
        'file'    => 'The :attribute must be less than or equal :value kilobytes.',
        'string'  => 'The :attribute must be less than or equal :value characters.',
        'array'   => 'The :attribute must not have more than :value items.',
    ],
    'max'                            => [
        'numeric' => 'The :attribute may not be greater than :max.',
        'file'    => 'The :attribute may not be greater than :max kilobytes.',
        'string'  => 'The :attribute may not be greater than :max characters.',
        'array'   => 'The :attribute may not have more than :max items.',
    ],
    'mimes'                          => 'The :attribute must be a file of type: :values.',
    'mimetypes'                      => 'The :attribute must be a file of type: :values.',
    'min'                            => [
        'numeric' => 'The :attribute must be at least :min.',
        'file'    => 'The :attribute must be at least :min kilobytes.',
        'string'  => 'The :attribute must be at least :min characters.',
        'array'   => 'The :attribute must have at least :min items.',
    ],
    'not_in'                         => 'The selected :attribute is invalid.',
    'not_regex'                      => 'The :attribute format is invalid.',
    'numeric'                        => 'The :attribute must be a number.',
    'password'                       => 'كلمة مرور خاطئة',
    'present'                        => 'The :attribute field must be present.',
    'regex'                          => 'The :attribute format is invalid.',
    'required'                       => 'حقل :attribute مطلوب',
    'required_if'                    => 'The :attribute field is required when :other is :value.',
    'required_unless'                => 'The :attribute field is required unless :other is in :values.',
    'required_with'                  => 'The :attribute field is required when :values is present.',
    'required_with_all'              => 'The :attribute field is required when :values is present.',
    'required_without'               => 'The :attribute field is required when :values is not present.',
    'required_without_all'           => 'The :attribute field is required when none of :values are present.',
    'same'                           => 'The :attribute and :other must match.',
    'size'                           => [
        'numeric' => 'The :attribute must be :size.',
        'file'    => 'The :attribute must be :size kilobytes.',
        'string'  => 'The :attribute must be :size characters.',
        'array'   => 'The :attribute must contain :size items.',
    ],
    'starts_with'                    => 'The :attribute must start with one of the following: :values.',
    'string'                         => ':attribute يجب ان يكون احرف',
    'timezone'                       => 'The :attribute must be a valid zone.',
    'unique'                         => ':attribute مأخوذ من قبل',
    //'uploaded'                       => 'فشل التحميل :attribute .',
    'uploaded'                     => 'يرجي اختيار صورة صالحة لا تتعدي 1 ميجا بايت',
    'url'                            => ':attribute نوعة غير صحيح',
    'uuid'                           => 'The :attribute must be a valid UUID.',
    'custom'                         => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],

        // users section
            'password' => [
                'required'  => __('ahmed.password_required'), 
            ],

             // added new validation
        // search index section
        'search_index' => [
                'required' => 'type something in search field .....',
            ],

            // add vendor section
            'vendor_name' => [
                'string'    => __('ahmed.vendor_name_string'),
                'required'  => __('ahmed.vendor_name_required'),
                'min'       => __('ahmed.vendor_name_min'),
                'unique'    => __('ahmed.vendor_name_unique'),
            ],

            'email'       => [
                'required'   => __('ahmed.enter_email'), 
                'email'      => __('ahmed.email_format'),
                'unique'     => __('ahmed.unique_email'),
                'email'      => __('ahmed.vendor_email_email'),
            ],

            'type'        => [
                'required' => __('ahmed.enter_type'), 
                'Rule'     => __('ahmed.enter_type_options'), 
            ],

            'userid_id'   => [
                'required' => __('ahmed.enter_userid_id'),
                'integer'  => __('ahmed.userid_id_integer'),
                'exists'   => __('ahmed.userid_id_exists'),
            ],
            'images'      => [
                'required'  => __('ahmed.enter_image'), 
                'file'      => __('ahmed.image_file'), 
                'image'     => __('ahmed.image_image'), 
                'mimes'     => __('ahmed.image_mimes'),
            ],

            'photo'      => [
                'required'  => __('ahmed.enter_image'), 
                'file'      => __('ahmed.image_file'), 
                'image'     => __('ahmed.image_image'), 
                'mimes'     => __('ahmed.image_mimes'),
            ],
            // end vendor section

            // car year section
            'year' => [
                'integer'      => __('ahmed.year_integer'), 
                'min'          => __('ahmed.year_min'), 
                'max'          => __('ahmed.year_max'), 
                'required'     => __('ahmed.year_required'), 
                'unique'       => __('ahmed.year_unique'), 
            ],
            // end car year section

            // car model section
            'carmade_id' => [
                'required'   => __('ahmed.carmade_required'), 
                'integer'    => __('ahmed.carmade_integer'), 
                'exists'     => __('ahmed.carmade_exists'), 
            ],

            'carmodel'   => [
                'string'      => __('ahmed.carmodel_string'), 
                'required'    => __('ahmed.carmodel_required'), 
                'unique'      => __('ahmed.carmodel_unique'), 
            ],
             // end car model section

            // car made section
            'categoryid_id' => [
                'required'  => __('ahmed.categoryid_id_required'), 
                'integer'   => __('ahmed.categoryid_id_integer'), 
                'exists'    => __('ahmed.categoryid_id_exists'), 
            ],
            'car_made'      => [
                'string'     => __('ahmed.car_made_string'), 
                'required'   => __('ahmed.car_made_required'), 
                'unique'     => __('ahmed.car_made_unique'), 
            ],
            // end car made section

            // part categories section
            'category_name' => [
                'string'      => __('ahmed.category_name_string'), 
                'required'    => __('ahmed.category_name_required'), 
                'unique'      => __('ahmed.category_name_unique'), 
            ],
            // end part categories section

            // stores section
            'address' => [
                'required'  => __('ahmed.store_address_required'), 
                'string'    => __('ahmed.store_address_string'), 
            ],

            'lat' => [
                'required'  => __('ahmed.store_lat_required'), 
                'regex'     => __('ahmed.store_lat_regex'), 
            ],

             'long' => [
                'required' => __('ahmed.store_long_required'), 
                'regex'    =>  __('ahmed.store_long_regex'), 
            ],

            'moderator_name' => [
                'required'  => __('ahmed.store_moderator_name_required'), 
                'string'    => __('ahmed.store_moderator_name_string'), 
            ],

            'moderator_phone' => [
                'required' => __('ahmed.store_moderator_phone_required'), 
                'regex'    => __('ahmed.store_moderator_phone_regex'), 
            ],

             'moderator_alt_phone' => [
                'nullable'   => __('ahmed.store_moderator_alt_phone_nullable'), 
                'regex'      => __('ahmed.store_moderator_alt_phone_regex'), 
            ],
             // end stores section

            // roles section
            'title'         => [
                'string'     => __('ahmed.title_string'), 
                'required'   => __('ahmed.title_required'), 
                'unique'     => __('ahmed.title_unique'), 
            ],

            'permissions'   => [
                'required' => __('ahmed.permissions_required'), 
            ],
            // end roles section

            // users section
            'password' => [
                'required'  => __('ahmed.password_required'), 
            ],

            'roles'    => [
                'required' => __('ahmed.roles_required'), 
            ],
            // end users section

            // products section
            'categories'       => [
                'required' => __('ahmed.product_categories_required'),
            ],
            'tags'       => [
                'required' => __('ahmed.product_tags_required'), 
            ],
            'car_made_id'      => [
                'required' => __('ahmed.product_car_made_id_required'), 
                'integer'  => __('ahmed.product_car_made_id_integer'), 
                'exists'   => __('ahmed.product_car_made_id_exists'), 
            ],
            'car_model_id'     => [
                'required'   => __('ahmed.product_car_model_id_required'), 
                'integer'    => __('ahmed.product_car_model_id_required'), 
                'exists'     => __('ahmed.product_car_model_id_required'), 
            ],
            'year_id'          => [
                'required' => __('ahmed.product_car_year_id_required'), 
                'integer'  => __('ahmed.product_car_year_id_integer'), 
                'exists'   => __('ahmed.product_car_year_id_required'), 
            ],
            'part_category_id' => [
                'required'  => __('ahmed.product_part_category_id_required'), 
                'integer'   => __('ahmed.product_part_category_id_integer'), 
                'exists'    => __('ahmed.product_part_category_id_exists'), 
            ],

            'description'  => [
                'string'   => __('ahmed.product_description_string'), 
                'required' => __('ahmed.product_description_required'), 
                'min'      => __('ahmed.product_description_min'), 
            ],
            'price'       => [
                'required'  => __('ahmed.product_price_required'), 
                'numeric'   => __('ahmed.product_price_numeric'), 
                'min'       => __('ahmed.product_price_min'), 
            ],
            'discount'            => [
               // 'nullable' => __('ahmed.product_part_category_id_exists'), 
                'numeric'  => __('ahmed.product_discount_numeric'), 
                'min'      => __('ahmed.product_discount_min'), 
                'max'      => __('ahmed.product_discount_max'), 
            ],
            'serial_number'   => [
                'required' => __('ahmed.product_serial_number_required'), 
                'unique'   => __('ahmed.product_serial_number_unique'), 
            ],
            'store_id' => [
                'required'  => __('ahmed.product_store_id_required'), 
                'integer'   => __('ahmed.product_store_id_integer'), 
                'exists'    => __('ahmed.product_store_id_exists'), 
            ],
             'quantity' => [
                'required'  => __('ahmed.product_quantity_required'), 
                'integer'   => __('ahmed.product_quantity_integer'), 
                'min'       => __('ahmed.product_quantity_min'), 
            ],
            // end products section

            // help center section
            'question' => [
                'required' => __('ahmed.question_required'),
            ],

            'answer' => [
                'required' => __('ahmed.answer_required'),
            ],

            'phone_no' => [
                'required'  => __('ahmed.phone_no_required'),
                'min'       => __('ahmed.phone_no_min'),
                'max'       => __('ahmed.phone_no_max'),
            ],
    ],
    'reserved_word'                  => 'The :attribute contains reserved word',
    'dont_allow_first_letter_number' => 'حقل الادخال \":input\" لايمكن ان يكون اول خانة رقم',
    'exceeds_maximum_number'         => 'ال :attribute وصل الحد الاقصى للمودل',
    'db_column'                      => 'ال :attribute يمكن ان يحتوى فقط على ترميز الايزو للاحراف اللاتينية وارقام وعلامة الداش ولايمكن ان يبدأ برقم',
    'attributes'                     => [],
];
