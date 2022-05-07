<?php

return [
    'userManagement'    => [
        'title'          => 'إدارة المستخدمين',
        'title_singular' => 'إدارة المستخدمين',
    ],
    'permission'        => [
        'title'          => 'الصلاحيات',
        'title_singular' => 'الصلاحية',
        'fields'         => [
            'id'                => 'ID',
            'id_helper'         => ' ',
            'title'             => 'Title',
            'title_helper'      => ' ',
            'created_at'        => 'Created at',
            'created_at_helper' => ' ',
            'updated_at'        => 'Updated at',
            'updated_at_helper' => ' ',
            'deleted_at'        => 'Deleted at',
            'deleted_at_helper' => ' ',
        ],
    ],
    'role'              => [
        'title'          => 'أدوار',
        'title_singular' => 'دور',
        'fields'         => [
            'id'                 => 'ID',
            'id_helper'          => ' ',
            'title'              => 'Title',
            'title_helper'       => ' ',
            'permissions'        => 'Permissions',
            'permissions_helper' => ' ',
            'created_at'         => 'Created at',
            'created_at_helper'  => ' ',
            'updated_at'         => 'Updated at',
            'updated_at_helper'  => ' ',
            'deleted_at'         => 'Deleted at',
            'deleted_at_helper'  => ' ',
        ],
    ],
    'user'              => [
        'title'          => 'المستخدمين',
        'title_singular' => 'مستخدم',
        'fields'         => [
            'id'                       => 'ID',
            'id_helper'                => ' ',
            'name'                     => 'Name',
            'name_helper'              => ' ',
            'email'                    => 'Email',
            'email_helper'             => ' ',
            'email_verified_at'        => 'Email verified at',
            'email_verified_at_helper' => ' ',
            'password'                 => 'Password',
            'password_helper'          => ' ',
            'roles'                    => 'Roles',
            'roles_helper'             => ' ',
            'remember_token'           => 'Remember Token',
            'remember_token_helper'    => ' ',
            'created_at'               => 'Created at',
            'created_at_helper'        => ' ',
            'updated_at'               => 'Updated at',
            'updated_at_helper'        => ' ',
            'deleted_at'               => 'Deleted at',
            'deleted_at_helper'        => ' ',
        ],
    ],
    'auditLog'          => [
        'title'          => 'Audit Logs',
        'title_singular' => 'Audit Log',
        'fields'         => [
            'id'                  => 'ID',
            'id_helper'           => ' ',
            'description'         => 'Description',
            'description_helper'  => ' ',
            'subject_id'          => 'Subject ID',
            'subject_id_helper'   => ' ',
            'subject_type'        => 'Subject Type',
            'subject_type_helper' => ' ',
            'user_id'             => 'User ID',
            'user_id_helper'      => ' ',
            'properties'          => 'Properties',
            'properties_helper'   => ' ',
            'host'                => 'Host',
            'host_helper'         => ' ',
            'created_at'          => 'Created at',
            'created_at_helper'   => ' ',
            'updated_at'          => 'Updated at',
            'updated_at_helper'   => ' ',
        ],
    ],
    'productManagement' => [

        'title'          => 'إدارة المنتجات',
        'title_singular' => 'Product Management',
    ],
    'productCategory'   => [
        'title'          => 'الأقسام', 
        'title_singular' => 'Product Management',
    ],
    'productCategory'   => [
        'title'          => 'Categories',
        'title_singular' => 'Category',
        'fields'         => [
            'id'                 => 'ID',
            'id_helper'          => ' ',
            'name'               => 'Name',
            'name_helper'        => ' ',
            'description'        => 'Description',
            'description_helper' => ' ',
            'photo'              => 'Photo',
            'photo_helper'       => ' ',
            'created_at'         => 'Created at',
            'created_at_helper'  => ' ',
            'updated_at'         => 'Updated At',
            'updated_at_helper'  => ' ',
            'deleted_at'         => 'Deleted At',
            'deleted_at_helper'  => ' ',
        ],
    ],
    'productTag'        => [
        'title'          => 'Tags',
        'title_singular' => 'Tag',
        'fields'         => [
            'id'                => 'ID',
            'id_helper'         => ' ',
            'name'              => 'Name',
            'name_helper'       => ' ',
            'created_at'        => 'Created at',
            'created_at_helper' => ' ',
            'updated_at'        => 'Updated At',
            'updated_at_helper' => ' ',
            'deleted_at'        => 'Deleted At',
            'deleted_at_helper' => ' ',
        ],
    ],
    'product'           => [

        'title'          => 'المنتجات',
        'title_singular' => 'Product',
        'fields'         => [
            'id'                   => 'ID',
            'id_helper'            => ' ',
            'name'                 => 'Name',
            'name_helper'          => ' ',
            'description'          => 'Description',
            'description_helper'   => ' ',
            'price'                => 'Price',
            'price_helper'         => ' ',
            'category'             => 'Categories',
            'category_helper'      => ' ',
            'photo'                => 'Photo',
            'photo_helper'         => ' ',
            'created_at'           => 'Created at',
            'created_at_helper'    => ' ',
            'updated_at'           => 'Updated At',
            'updated_at_helper'    => ' ',
            'deleted_at'           => 'Deleted At',
            'deleted_at_helper'    => ' ',
            'car_made'             => 'Car made',
            'car_made_helper'      => ' ',
            'car_model'            => 'Car Model',
            'car_model_helper'     => ' ',
            'year'                 => 'Year',
            'year_helper'          => ' ',
            'part_category'        => 'PartCategory',
            'part_category_helper' => ' ',
            'discount'             => 'Discount',
            'discount_helper'      => ' ',
        ],
    ],
    'carMade'           => [
        'title'          => 'Car made',
        'title_singular' => 'Car made',
        'fields'         => [
            'id'                => 'ID',
            'id_helper'         => ' ',
            'categoryid'        => 'Category',
            'categoryid_helper' => ' ',
            'car_made'          => 'Car made',
            'car_made_helper'   => ' ',
            'created_at'        => 'Created at',
            'created_at_helper' => ' ',
            'updated_at'        => 'Updated at',
            'updated_at_helper' => ' ',
            'deleted_at'        => 'Deleted at',
            'deleted_at_helper' => ' ',
        ],
    ],
    'carModel'          => [
        'title'          => 'Car Model',
        'title_singular' => 'Car Model',
        'fields'         => [
            'id'                => 'ID',
            'id_helper'         => ' ',
            'carmade'           => 'Car Made',
            'carmade_helper'    => ' ',
            'carmodel'          => 'Car Model',
            'carmodel_helper'   => ' ',
            'created_at'        => 'Created at',
            'created_at_helper' => ' ',
            'updated_at'        => 'Updated at',
            'updated_at_helper' => ' ',
            'deleted_at'        => 'Deleted at',
            'deleted_at_helper' => ' ',
        ],
    ],
    'partCategory'      => [
        'title'          => 'Part Category',
        'title_singular' => 'Part Category',
        'fields'         => [
            'id'                   => 'ID',
            'id_helper'            => ' ',
            'category_name'        => 'Category Name',
            'category_name_helper' => ' ',
            'created_at'           => 'Created at',
            'created_at_helper'    => ' ',
            'updated_at'           => 'Updated at',
            'updated_at_helper'    => ' ',
            'deleted_at'           => 'Deleted at',
            'deleted_at_helper'    => ' ',
            'photo'                => 'Photo',
            'photo_helper'         => ' ',
        ],
    ],
    'carYear'           => [
        'title'          => 'Car Year',
        'title_singular' => 'Car Year',
        'fields'         => [
            'id'                => 'ID',
            'id_helper'         => ' ',
            'year'              => 'Year',
            'year_helper'       => ' ',
            'created_at'        => 'Created at',
            'created_at_helper' => ' ',
            'updated_at'        => 'Updated at',
            'updated_at_helper' => ' ',
            'deleted_at'        => 'Deleted at',
            'deleted_at_helper' => ' ',
        ],
    ],
    'addVendor'         => [
        'title'          => 'Add Vendor',
        'title_singular' => 'Add Vendor',
        'fields'         => [
            'id'                 => 'ID',
            'id_helper'          => ' ',
            'vendor_name'        => 'Vendor Name',
            'vendor_name_helper' => ' ',
            'email'              => 'Email',
            'email_helper'       => ' ',
            'type'               => 'Type',
            'type_helper'        => ' ',
            'userid'             => 'User Name',
            'userid_helper'      => ' ',
            'images'             => 'Logo',
            'images_helper'      => ' ',
            'created_at'         => 'Created at',
            'created_at_helper'  => ' ',
            'updated_at'         => 'Updated at',
            'updated_at_helper'  => ' ',
            'deleted_at'         => 'Deleted at',
            'deleted_at_helper'  => ' ',
            'serial'             => 'Serial',
            'serial_helper'      => ' ',
        ],
    ],
    'vendor'            => [
        'title'          => 'Vendor',
        'title_singular' => 'Vendor',
    ],
];
