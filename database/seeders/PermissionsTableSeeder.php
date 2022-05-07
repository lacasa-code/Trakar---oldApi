<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            [
                'id'    => 1,
                'title' => 'user_management_access',
            ],
            [
                'id'    => 2,
                'title' => 'permission_create',
            ],
            [
                'id'    => 3,
                'title' => 'permission_edit',
            ],
            [
                'id'    => 4,
                'title' => 'permission_show',
            ],
            [
                'id'    => 5,
                'title' => 'permission_delete',
            ],
            [
                'id'    => 6,
                'title' => 'permission_access',
            ],
            [
                'id'    => 7,
                'title' => 'role_create',
            ],
            [
                'id'    => 8,
                'title' => 'role_edit',
            ],
            [
                'id'    => 9,
                'title' => 'role_show',
            ],
            [
                'id'    => 10,
                'title' => 'role_delete',
            ],
            [
                'id'    => 11,
                'title' => 'role_access',
            ],
            [
                'id'    => 12,
                'title' => 'user_create',
            ],
            [
                'id'    => 13,
                'title' => 'user_edit',
            ],
            [
                'id'    => 14,
                'title' => 'user_show',
            ],
            [
                'id'    => 15,
                'title' => 'user_delete',
            ],
            [
                'id'    => 16,
                'title' => 'user_access',
            ],
            [
                'id'    => 17,
                'title' => 'audit_log_show',
            ],
            [
                'id'    => 18,
                'title' => 'audit_log_access',
            ],
            [
                'id'    => 19,
                'title' => 'product_management_access',
            ],
            [
                'id'    => 20,
                'title' => 'product_category_create',
            ],
            [
                'id'    => 21,
                'title' => 'product_category_edit',
            ],
            [
                'id'    => 22,
                'title' => 'product_category_show',
            ],
            [
                'id'    => 23,
                'title' => 'product_category_delete',
            ],
            [
                'id'    => 24,
                'title' => 'product_category_access',
            ],
            [
                'id'    => 25,
                'title' => 'product_tag_create',
            ],
            [
                'id'    => 26,
                'title' => 'product_tag_edit',
            ],
            [
                'id'    => 27,
                'title' => 'product_tag_show',
            ],
            [
                'id'    => 28,
                'title' => 'product_tag_delete',
            ],
            [
                'id'    => 29,
                'title' => 'product_tag_access',
            ],
            [
                'id'    => 30,
                'title' => 'product_create',
            ],
            [
                'id'    => 31,
                'title' => 'product_edit',
            ],
            [
                'id'    => 32,
                'title' => 'product_show',
            ],
            [
                'id'    => 33,
                'title' => 'product_delete',
            ],
            [
                'id'    => 34,
                'title' => 'product_access',
            ],
            [
                'id'    => 35,
                'title' => 'car_made_create',
            ],
            [
                'id'    => 36,
                'title' => 'car_made_edit',
            ],
            [
                'id'    => 37,
                'title' => 'car_made_show',
            ],
            [
                'id'    => 38,
                'title' => 'car_made_delete',
            ],
            [
                'id'    => 39,
                'title' => 'car_made_access',
            ],
            [
                'id'    => 40,
                'title' => 'car_model_create',
            ],
            [
                'id'    => 41,
                'title' => 'car_model_edit',
            ],
            [
                'id'    => 42,
                'title' => 'car_model_show',
            ],
            [
                'id'    => 43,
                'title' => 'car_model_delete',
            ],
            [
                'id'    => 44,
                'title' => 'car_model_access',
            ],
            [
                'id'    => 45,
                'title' => 'part_category_create',
            ],
            [
                'id'    => 46,
                'title' => 'part_category_edit',
            ],
            [
                'id'    => 47,
                'title' => 'part_category_show',
            ],
            [
                'id'    => 48,
                'title' => 'part_category_delete',
            ],
            [
                'id'    => 49,
                'title' => 'part_category_access',
            ],
            [
                'id'    => 50,
                'title' => 'car_year_create',
            ],
            [
                'id'    => 51,
                'title' => 'car_year_edit',
            ],
            [
                'id'    => 52,
                'title' => 'car_year_show',
            ],
            [
                'id'    => 53,
                'title' => 'car_year_delete',
            ],
            [
                'id'    => 54,
                'title' => 'car_year_access',
            ],
            [
                'id'    => 55,
                'title' => 'add_vendor_create',
            ],
            [
                'id'    => 56,
                'title' => 'add_vendor_edit',
            ],
            [
                'id'    => 57,
                'title' => 'add_vendor_show',
            ],
            [
                'id'    => 58,
                'title' => 'add_vendor_delete',
            ],
            [
                'id'    => 59,
                'title' => 'add_vendor_access',
            ],
            [
                'id'    => 60,
                'title' => 'vendor_access',
            ],
            [
                'id'    => 61,
                'title' => 'profile_password_edit',
            ],
            [
                'id'    => 62,
                'title' => 'add_vendor_add_products',
            ],
            [
                'id'    => 63,
                'title' => 'add_vendor_access_products',
            ],
            [
                'id'    => 64,
                'title' => 'stores_access',
            ],
            [
                'id'    => 65,
                'title' => 'stores_show',
            ],
            [
                'id'    => 66,
                'title' => 'stores_create',
            ],
            [
                'id'    => 67,
                'title' => 'stores_edit',
            ],
            [
                'id'    => 68,
                'title' => 'stores_delete',
            ],
            // added new
            [
                'id'    => 69,
                'title' => 'orders_need_approval_access',
            ],
            [
                'id'    => 70,
                'title' => 'cancel_orders',
            ],
            [
                'id'    => 71,
                'title' => 'approve_orders',
            ],
            [
                'id'    => 72,
                'title' => 'show_invoices_access',
            ],
            [
                'id'    => 73,
                'title' => 'show_specific_invoice',
            ],
            [
                'id'    => 74,
                'title' => 'show_orders_access',
            ],
            [
                'id'    => 75,
                'title' => 'show_specific_order',
            ],
            [
                'id'    => 76,
                'title' => 'admin_access_vendor_invoices',
            ],
            [
                'id'    => 77,
                'title' => 'admin_access_vendor_orders',
            ],
            [
                'id'    => 78,
                'title' => 'admin_access_specific_vendor_specific_order',
            ],
            [
                'id'    => 79,
                'title' => 'admin_access_specific_vendor_specific_invoice',
            ],  
            [
                'id'    => 80,
                'title' => 'tickets_access',
            ],  
            [
                'id'    => 81,
                'title' => 'specific_ticket_access',
            ],  
            [
                'id'    => 82,
                'title' => 'access_tabs_collectively',
            ],  
            [
                'id'    => 83,
                'title' => 'access_tabs_separately',
            ],  
            [
                'id'    => 84,
                'title' => 'help_center_access',
            ],  
            [
                'id'    => 85,
                'title' => 'help_center_create',
            ],  
            [
                'id'    => 86,
                'title' => 'help_center_update',
            ],  
            [
                'id'    => 87,
                'title' => 'help_center_delete',
            ],  
            [
                'id'    => 88,
                'title' => 'help_center_show_specific',
            ],  
            [
                'id'    => 89,
                'title' => 'show_reports_stats',
            ],  
            // new 26 April 2021
            [
                'id'    => 90,
                'title' => 'manufacturers_access',
            ],  
            [
                'id'    => 91,
                'title' => 'manufacturers_add',
            ],  
            [
                'id'    => 92,
                'title' => 'manufacturers_show',
            ],  
            [
                'id'    => 93,
                'title' => 'manufacturers_update',
            ],  
            [
                'id'    => 94,
                'title' => 'manufacturers_delete',
            ],  
            [
                'id'    => 95,
                'title' => 'origin_countries_access',
            ],  
            [
                'id'    => 96,
                'title' => 'origin_countries_add',
            ],  
            [
                'id'    => 97,
                'title' => 'origin_countries_show',
            ],  
            [
                'id'    => 98,
                'title' => 'origin_countries_update',
            ],  
            [
                'id'    => 99,
                'title' => 'origin_countries_delete',
            ],  
            // new 26 April 2021
             // new 10 May 2021  
            [
                'id'    => 100,
                'title' => 'car_type_access',
            ],  
            [
                'id'    => 101,
                'title' => 'car_type_show',
            ],  
            [
                'id'    => 102,
                'title' => 'car_type_add',
            ],  
            [
                'id'    => 103,
                'title' => 'car_type_update',
            ],  
            [
                'id'    => 104,
                'title' => 'car_type_delete',
            ],  
            // new 10 May 2021  
             // new 11 May 2021  
            [
                'id'    => 105,
                'title' => 'advertisements_access',
            ],  
            [
                'id'    => 106,
                'title' => 'advertisements_show',
            ],  
            [
                'id'    => 107,
                'title' => 'advertisements_add',
            ],  
            [
                'id'    => 108,
                'title' => 'advertisements_edit',
            ],  
            [
                'id'    => 109,
                'title' => 'advertisements_delete',
            ],  
             // new 11 May 2021  





            // june 16 2021
            [
                'id'    => 110,
                'title' => 'country_create',
            ],  
            [
                'id'    => 111,
                'title' => 'country_update',
            ],  
            [
                'id'    => 112,
                'title' => 'country_show',
            ],  
            [
                'id'    => 113,
                'title' => 'country_delete',
            ],  
            [
                'id'    => 114,
                'title' => 'countries_access',
            ], 
            [
                'id'    => 115,
                'title' => 'area_delete',
            ],  
            [
                'id'    => 116,
                'title' => 'area_update',
            ],  
            [
                'id'    => 117,
                'title' => 'area_create',
            ],  
            [
                'id'    => 118,
                'title' => 'areas_access',
            ],  
            [
                'id'    => 119,
                'title' => 'area_show',
            ],  
            [
                'id'    => 120,
                'title' => 'city_delete',
            ],  
            [
                'id'    => 121,
                'title' => 'city_update',
            ],  
            [
                'id'    => 122,
                'title' => 'city_create',
            ],  
            [
                'id'    => 123,
                'title' => 'city_show',
            ],  
            [
                'id'    => 124,
                'title' => 'cities_access',
            ],  
            // june 19 2021
            [
                'id'    => 125,
                'title' => 'main_categories_access',
            ],  
            [
                'id'    => 126,
                'title' => 'main_category_show',
            ],  
            [
                'id'    => 127,
                'title' => 'main_category_create',
            ],  
            [
                'id'    => 128,
                'title' => 'main_category_update',
            ],
            [
                'id'    => 129,
                'title' => 'main_category_delete',
            ],
            // june 23 2021
            [
                'id'    => 130,
                'title' => 'vendor_add_staff',
            ],
             // june 23 2021
            [
                'id'    => 131,
                'title' => 'user_access_by_vendor',
            ],
             // june 23 2021
            [
                'id'    => 132,
                'title' => 'user_create_by_vendor',
            ],
             // june 23 2021
            [
                'id'    => 133,
                'title' => 'user_show_by_vendor',
            ],
             // june 23 2021
            [
                'id'    => 134,
                'title' => 'user_edit_by_vendor',
            ],
             // june 23 2021
            [
                'id'    => 135,
                'title' => 'user_delete_by_vendor',
            ],
             // July 27 2021
            [
                'id'    => 136,
                'title' => 'vendor_answer_question',
            ],
            [
                'id'    => 137,
                'title' => 'fetch_vendor_questions',
            ],
            [
                'id'    => 138,
                'title' => 'access_questions',
            ],
            [
                'id'    => 139,
                'title' => 'wholesale_orders_access',
            ],
            [
                'id'    => 140,
                'title' => 'wholesale_invoices_access',
            ],
        ];

        foreach ($permissions as $permission)
        {
            Permission::firstOrCreate([
                'id'    => $permission['id'],
                'title' => $permission['title'],
            ]);
        }
    }
}
