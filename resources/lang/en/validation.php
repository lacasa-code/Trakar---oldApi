<?php
return [
    'accepted'                       => 'The :attribute must be accepted.',
    'active_url'                     => 'The :attribute is not a valid URL.',
    'after'                          => 'The :attribute must be a date after :date.',
    'after_or_equal'                 => 'The :attribute must be a date after or equal to :date.',
    'alpha'                          => 'The :attribute may only contain letters.',
    'alpha_dash'                     => 'The :attribute may only contain letters, numbers, and dashes.',
    'alpha_num'                      => 'The :attribute may only contain letters and numbers.',
    'latin'                          => 'The :attribute may only contain ISO basic Latin alphabet letters.',
    'latin_dash_space'               => 'The :attribute may only contain ISO basic Latin alphabet letters, numbers, dashes, hyphens and spaces.',
    'array'                          => 'The :attribute must be an array.',
    'before'                         => 'The :attribute must be a date before :date.',
    'before_or_equal'                => 'The :attribute must be a date before or equal to :date.',
    'between'                        => [
        'numeric' => 'The :attribute must be between :min and :max.',
        'file'    => 'The :attribute must be between :min and :max kilobytes.',
        'string'  => 'The :attribute must be between :min and :max characters.',
        'array'   => 'The :attribute must have between :min and :max items.',
    ],
    'boolean'                        => 'The :attribute field must be true or false.',
    'confirmed'                      => 'The :attribute confirmation does not match.',
    'date'                           => 'The :attribute is not a valid date.',
    'date_equals'                    => 'The :attribute must be a date equal to :date.',
    'date_format'                    => 'The :attribute does not match the format :format.',
    'different'                      => 'The :attribute and :other must be different.',
    'digits'                         => 'The :attribute must be :digits digits.',
    'digits_between'                 => 'The :attribute must be between :min and :max digits.',
    'dimensions'                     => 'The :attribute has invalid image dimensions.',
    'distinct'                       => 'The :attribute field has a duplicate value.',
    'email'                          => 'The :attribute must be a valid email address.',
    'ends_with'                      => 'The :attribute must end with one of the following: :values.',
    'exists'                         => 'The selected :attribute is invalid.',
    'file'                           => 'The :attribute must be a file.',
    'filled'                         => 'The :attribute field must have a value.',
    'gt'                             => [
        'numeric' => 'The :attribute must be greater than :value.',
        'file'    => 'The :attribute must be greater than :value kilobytes.',
        'string'  => 'The :attribute must be greater than :value characters.',
        'array'   => 'The :attribute must have more than :value items.',
    ],
    'gte'                            => [
        'numeric' => 'The :attribute must be greater than or equal :value.',
        'file'    => 'The :attribute must be greater than or equal :value kilobytes.',
        'string'  => 'The :attribute must be greater than or equal :value characters.',
        'array'   => 'The :attribute must have :value items or more.',
    ],
    'image'                          => 'The :attribute must be an image.',
    'in'                             => 'The selected :attribute is invalid.',
    'in_array'                       => 'The :attribute field does not exist in :other.',
    'integer'                        => 'The :attribute must be an integer.',
    'ip'                             => 'The :attribute must be a valid IP address.',
    'ipv4'                           => 'The :attribute must be a valid IPv4 address.',
    'ipv6'                           => 'The :attribute must be a valid IPv6 address.',
    'json'                           => 'The :attribute must be a valid JSON string.',
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
    'password'                       => 'The password is incorrect.',
    'present'                        => 'The :attribute field must be present.',
    'regex'                          => 'The :attribute format is invalid.',
    'required'                       => 'The :attribute field is required.',
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
    'string'                         => 'The :attribute must be a string.',
    'timezone'                       => 'The :attribute must be a valid zone.',
    'unique'                         => 'The :attribute has already been taken.',
  //  'uploaded'                       => 'The :attribute failed to upload.',
    'uploaded'                       => 'please select valid image format with max size 1 MB',
    'url'                            => 'The :attribute format is invalid.',
    'uuid'                           => 'The :attribute must be a valid UUID.',
    'custom'                         => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
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
            // end help center section
    ],
    'reserved_word'                  => 'The :attribute contains reserved word',
    'dont_allow_first_letter_number' => 'The \":input\" field can\'t have first letter as a number',
    'exceeds_maximum_number'         => 'The :attribute exceeds maximum model length',
    'db_column'                      => 'The :attribute may only contain ISO basic Latin alphabet letters, numbers, dash and cannot start with number.',
    'attributes'                     => [],
];
