<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Str;
use App\Models\ProductCategory;
use App\Models\PartCategory;
use App\Models\CarYear;
use App\Models\CarMade;
use App\Models\CarModel;
use App\Models\Product;
use App\Models\AddVendor;
use App\Models\Store;

use App\Models\Manufacturer;
use App\Models\Transmission;
use App\Models\Prodcountry;
use App\Models\ProductTag;
use App\Models\Producttype;
use App\Models\Cartype;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$categories = [
            [
                'id'             => 1,
                'name'           => 'first category',
                'description'    => 'first category description',
            ],
        ];

        $tags = [
            [
                'id'             => 1,
                'name'           => 'first category',
            ],
        ];

        $origings = [
            [
                'id'             => 1,
                'country_name'           => 'first origing',
            ],
        ];

        $transmissions = [
            [
                'id'             => 1,
                'transmission_name'           => 'first transmission',
            ],
        ];

        $producttypes = [
            [
                'id'             => 1,
                'producttype'           => 'first producttype',
            ],
        ];

        $manufacturers = [
            [
                'id'             => 1,
                'manufacturer_name'           => 'first category',
            ],
        ];

        $cartypes = [
            [
                'id'             => 1,
                'type_name'           => 'first type',
            ],
        ];

        $part_categories = [
            [
                'id'             => 1,
                'category_name'           => 'first part category',
            ],
        ];

        $years = [
            [
                'id'             => 1,
                'year'           => '2010',
            ],
        ];

        $mades = [
            [
                'id'             => 1,
                'car_made'       => 'first made',
                'categoryid_id'  => 1,
            ],
        ];

        $models = [
            [
                'id'             => 1,
                'carmodel'       => 'first model',
                'carmade_id'     => 1,
            ],
        ];

         $vendors = [
            [
                'id'                => 1,
                'vendor_name'       => 'first vendor',
                'email'             => 'vendor@vendor.com',
                'serial'            => 'v001',
                'type'              => 'vendor',   
                'userid_id'         => 1,             
            ],
        ];

        $stores = [
            [
                'id'                    => 1,
                'name'                  => 'first store',
                'address'               => 'first store address',
		    	'lat'                   => '40.1111111',
		    	'long'                  => '40.1111111',
		    	'vendor_id'             => 1,
		    	'moderator_name'        => 'moderator',
		    	'moderator_phone'       => '96611111111',
		    	'moderator_alt_phone'   => '96611111111',
                
            ],
        ];

        $products = [
            [
                'id'                => 1,
                'name'              => 'first product',
                'price'             => 10,
                'discount'          => 1,
                'car_made_id'       => 1,
		        'car_model_id'      => 1,
		        'year_id'           => 1,
		        'part_category_id'  => 1,
		        'vendor_id'         => 1,
		        'description'       => 'first product description',
                'store_id'          => 1,
                'quantity'          => 10,
                'serial_number'     => Str::random(10),
            ],
        ];

        ProductCategory::insert($categories);
        PartCategory::insert($part_categories);
        CarYear::insert($years);
        CarMade::insert($mades);
        CarModel::insert($models);
        AddVendor::insert($vendors);
        Store::insert($stores);
        Product::insert($products);
        Manufacturer::insert($manufacturers);
        Transmission::insert($transmissions);
        Prodcountry::insert($origings);
        ProductTag::insert($tags);
        Producttype::insert($producttypes);
        Cartype::insert($cartypes);
        // $product = Product::find(1);
        /*$prod_categories = [
            [
                'product_category_id'             => 1,
                'product_id'                      => 1,
            ],
        ];*/
        // $product->categories()->sync($prod_categories);
    }
}
