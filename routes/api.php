<?php
/*********************************************************************/
// stable pagination constant default 10
// define('PAGINATION_COUNT', 10);
/*********************************************************************/


/*********************************************************************/
// Route::group(['middleware' => ['cors', 'json.response']], function () {

// start login
Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'Api\V1\Admin'], function () 
{
   Route::get('/login', 'UsersApiController@getLogin')->name('admin.getLogin');
   Route::post('/login', 'UsersApiController@login')->name('admin.login');//->middleware('csrf');
});
// end login
/*********************************************************************/

// Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'Api\V1\Admin', 'middleware' => ['auth:sanctum', 'csrf']], function () {

// group prefix and middleware for following routes 
/*Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'Api\V1\Admin', 'middleware' => ['auth:sanctum']], function () {*/

Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'Api\V1\Admin', 'middleware' => ['auth:api']], function () {


/*********************************************************************/
    // logout section 
Route::post('logout', 'UsersApiController@logout'); 
/*********************************************************************/
Route::post('token/data', 'TokenApiController@fetch_data')->name('token.fetch_data'); 

// refresh token 
Route::post('token/refresh', 'TokenApiController@token_refresh')->name('token.token_refresh'); 

/*********************************************************************/
    // Permissions
    // Route::apiResource('permissions', 'PermissionsApiController');
Route::get('permissions', 'PermissionsApiController@index')->name('permissions.index');
Route::post('permissions', 'PermissionsApiController@store')->name('permissions.store');
// disable edit permissions
Route::put('permissions/{permission}', 'PermissionsApiController@update')
      ->name('permissions.update');
Route::delete('permissions/{permission}', 'PermissionsApiController@destroy')
      ->name('permissions.destroy');
Route::get('permissions/{permission}', 'PermissionsApiController@show')
     ->name('permissions.show');
/*********************************************************************/

/*********************************************************************/
    // Roles
    //Route::apiResource('roles', 'RolesApiController');
Route::get('roles', 'RolesApiController@index')->name('roles.index');
Route::post('roles', 'RolesApiController@store')->name('roles.store');
Route::put('roles/{id}', 'RolesApiController@update')->name('roles.update');
Route::delete('roles/{role}', 'RolesApiController@destroy')->name('roles.destroy');
Route::get('roles/{role}', 'RolesApiController@show')->name('roles.show');
/*********************************************************************/

/*********************************************************************/
    // Users
    // Route::apiResource('users', 'UsersApiController');
Route::get('users', 'UsersApiController@index')->name('users.index');
Route::post('users', 'UsersApiController@store')->name('users.store');
Route::put('users/{user}', 'UsersApiController@update')->name('users.update');
Route::delete('users/{user}', 'UsersApiController@destroy')->name('users.destroy');
Route::get('users/{user}', 'UsersApiController@show')->name('users.show');
    // search users with name
Route::post('users/search/name', 'UsersApiController@search_with_name')
      ->name('users.search_with_name');
    // edit profile
Route::post('edit/profile', 'ProfileApiController@edit_profile')->name('profile.edit');
    // change password
Route::post('change/password', 'ProfileApiController@change_password')->name('profile.change_password');
    // delete account stopped right now
/*********************************************************************/

/*********************************************************************/
    // audit logs
Route::get('audit-logs', 'AuditLogsApiController@index')
     ->name('audit-logs.index');

Route::get('audit-logs/{auditLog}', 'AuditLogsApiController@show')
     ->name('audit-logs.show');

 // start search audit-logs with name
Route::post('audit-logs/search/name', 'AuditLogsApiController@search_with_name')
     ->name('audit-logs.search_with_name');
/*********************************************************************/

/*********************************************************************/
    // Product Categories
Route::post('product-categories/media', 'ProductCategoryApiController@storeMedia')
     ->name('product-categories.storeMedia');
//    Route::apiResource('product-categories', 'ProductCategoryApiController');
Route::get('product-categories', 'ProductCategoryApiController@index')
     ->name('product-categories.index');
Route::post('product-categories', 'ProductCategoryApiController@store')
     ->name('product-categories.store');
Route::post('product-categories/{productCategory}', 'ProductCategoryApiController@update')
     ->name('product-categories.update');
Route::delete('product-categories/{productCategory}', 'ProductCategoryApiController@destroy')
     ->name('product-categories.destroy');
Route::get('product-categories/{productCategory}', 'ProductCategoryApiController@show')
     ->name('product-categories.show');
 // start search categories with name
Route::post('categories/search/name', 'ProductCategoryApiController@search_with_name')
     ->name('product-categories.search_with_name');
/*********************************************************************/

/*********************************************************************/
    // Product Tags
    // Route::apiResource('product-tags', 'ProductTagApiController');
Route::get('product-tags', 'ProductTagApiController@index')->name('product-tags.index');
Route::post('product-tags', 'ProductTagApiController@store')->name('product-tags.store');
Route::put('product-tags/{productTag}', 'ProductTagApiController@update')
     ->name('product-tags.update');
Route::delete('product-tags/{productTag}', 'ProductTagApiController@destroy')
     ->name('product-tags.destroy');
Route::get('product-tags/{productTag}', 'ProductTagApiController@show')
     ->name('product-tags.show');


Route::get('g/qr', 'QrCodeApiController@generate');

/*********************************************************************/

/*********************************************************************/
    // Products
Route::post('products/media', 'ProductApiController@storeMedia')->name('products.storeMedia');
Route::post('mark/default/media', 'ProductApiController@mark_default_media')->name('products.mark_default_media');
//    Route::apiResource('products', 'ProductApiController');

Route::get('products', 'ProductApiController@index')->name('products.index');

Route::get('sss', 'ProductApiController@ss')->name('products.ss');

Route::post('add/products', 'ProductApiController@add_product_v2')->name('products.add_product');
// june 21 2021
Route::post('approve/products', 'ApprovedProductsApiController@approve_product')->name('products.approve_product');
Route::get('products/need/approval', 'ApprovedProductsApiController@products_need_approval')->name('products.products_need_approval');

/* translation add update */
Route::post('add/products/trans', 'ProductApiController@add_product_v2')->name('products.add_product_v2');
Route::post('products/trans/{product}', 'ProductApiController@update_v2')->name('products.update_v2');
/* translation add update */

Route::post('products/{product}', 'ProductApiController@update_v2')->name('products.update');
Route::delete('products/{product}', 'ProductApiController@destroy')->name('products.destroy');
Route::get('products/{product}', 'ProductApiController@show')->name('products.show');

Route::post('products/remove/checked/media', 'ProductApiController@remove_checked_media')
    ->name('products.remove_checked_media');

/******************************************************************************************/
Route::post('edit/type/price/{id}', 'Price\ProductPriceApiController@edit_type_price')
->name('products.edit_type_price');
/******************************************************************************************/
// hole search products
Route::post('products/hole/search', 'ProductApiController@hole_search_products')
     ->name('products.hole_search_products');
 
 // start search products with name
Route::post('products/search/name', 'ProductApiController@search_with_name')
     ->name('products.search_with_name');

// start search products with price
Route::post('products/search/price', 'ProductApiController@search_with_price')
     ->name('products.search_with_price');

/* dynamic column search */
// start search products with price
Route::post('products/search/dynamic', 'ProductsSearchDashboardApiController@search_dynamic_columns')
     ->name('products.search_dynamic_columns');

// start search products with car made 
Route::post('products/search/carmade', 'ProductApiController@search_with_car_made')
     ->name('products.search_with_car_made');

// start search products with car model 
Route::post('products/search/carmodel', 'ProductApiController@search_with_car_model')
     ->name('products.search_with_car_model');

// start search products with car year 
Route::post('products/search/caryear', 'ProductApiController@search_with_car_year')
     ->name('products.search_with_car_year');

// start search products with category 
Route::post('products/search/category', 'ProductApiController@search_with_product_category')
     ->name('products.search_with_product_category');

// start search products with part category
Route::post('products/search/part/category', 'ProductApiController@search_with_part_category')
     ->name('products.search_with_part_category');
/*********************************************************************/

/*********************************************************************/
// Car Mades
// Route::apiResource('car-mades', 'CarMadeApiController');
Route::get('car-mades', 'CarMadeApiController@index')->name('car-mades.index');
Route::post('car-mades', 'CarMadeApiController@store')->name('car-mades.store');
Route::put('car-mades/{id}', 'CarMadeApiController@update')->name('car-mades.update');
Route::delete('car-mades/{carMade}', 'CarMadeApiController@destroy')->name('car-mades.destroy');
Route::get('car-mades/{carMade}', 'CarMadeApiController@show')->name('car-mades.show');
     // start search car mades with name
Route::post('car-mades/search/name', 'CarMadeApiController@search_with_name')
     ->name('car-mades.search_with_name');
/*********************************************************************/

// june 16 2021

/*********************************************************************/
// Route::apiResource('car-mades', 'CarMadeApiController');
Route::get('countries', 'CountryApiController@index')->name('Country.index');
Route::post('countries', 'CountryApiController@store')->name('Country.store');
Route::post('countries/{id}', 'CountryApiController@update')->name('Country.update');
Route::delete('countries/{id}', 'CountryApiController@destroy')->name('Country.destroy');
Route::get('countries/{id}', 'CountryApiController@show')->name('Country.show');
Route::get('countries/list/all', 'CountryApiController@list_all')->name('Country.list_all');
     // start search Country with name
Route::post('countries/search/name', 'CountryApiController@search_with_name')
     ->name('Country.search_with_name');
Route::post('countries/mass/delete', 'CountryApiController@mass_delete')
     ->name('Country.mass_delete');
/*********************************************************************/

/*********************************************************************/
// Route::apiResource('car-mades', 'CarMadeApiController');
Route::get('areas', 'AreaApiController@index')->name('areas.index');
Route::post('areas', 'AreaApiController@store')->name('areas.store');
Route::post('areas/{id}', 'AreaApiController@update')->name('areas.update');
Route::delete('areas/{id}', 'AreaApiController@destroy')->name('areas.destroy');
Route::get('areas/{id}', 'AreaApiController@show')->name('areas.show');
Route::get('areas/list/all/{id}', 'AreaApiController@list_all')->name('areas.list_all');
Route::get('areas/list/all', 'AreaApiController@list_all_list')->name('areas.list_all');
     // start search car mades with name
Route::post('areas/search/name', 'AreaApiController@search_with_name')
     ->name('areas.search_with_name');
Route::post('areas/mass/delete', 'AreaApiController@mass_delete')
     ->name('areas.mass_delete');
/*********************************************************************/

/*********************************************************************/
// Route::apiResource('car-mades', 'CarMadeApiController');
Route::get('cities', 'CityApiController@index')->name('cities.index');
Route::post('cities', 'CityApiController@store')->name('cities.store');
Route::post('cities/{id}', 'CityApiController@update')->name('cities.update');
Route::delete('cities/{id}', 'CityApiController@destroy')->name('cities.destroy');
Route::get('cities/{id}', 'CityApiController@show')->name('cities.show');
Route::get('cities/list/all/{id}', 'CityApiController@list_all')->name('cities.list_all');
Route::get('cities/list/all', 'CityApiController@list_all_list')->name('cities.list_all');

     // start search car mades with name
Route::post('cities/search/name', 'CityApiController@search_with_name')
     ->name('cities.search_with_name');
Route::post('cities/mass/delete', 'CityApiController@mass_delete')
     ->name('cities.mass_delete');
/*********************************************************************/

/*********************************************************************/
// Route::apiResource('car-mades', 'CarMadeApiController');
Route::get('main/categories', 'MainCategories\MainCtaegoryApiController@index')
->name('mainCategories.index');
Route::post('main/categories', 'MainCategories\MainCtaegoryApiController@store')
->name('mainCategories.store');
Route::post('main/categories/{id}', 'MainCategories\MainCtaegoryApiController@update')
->name('mainCategories.update');
Route::delete('main/categories/{id}', 'MainCategories\MainCtaegoryApiController@destroy')
->name('mainCategories.destroy');
Route::get('main/categories/{id}', 'MainCategories\MainCtaegoryApiController@show')
->name('mainCategories.show');
Route::get('main/categories/list/all', 'MainCategories\MainCtaegoryApiController@list_all')
->name('mainCategories.list_all');
     // start search car mades with name
Route::post('main/categories/search/name', 'MainCategories\MainCtaegoryApiController@search_with_name')
->name('mainCategories.search_with_name');
Route::post('main/categories/mass/delete', 'MainCategories\MainCtaegoryApiController@mass_delete')->name('mainCategories.mass_delete');
Route::get('main/categories/list/nested/{id}', 'MainCategories\MainCtaegoryApiController@list_specific_fetched')
->name('mainCategories.list_all');
/*********************************************************************/

// june 16 2021


/*********************************************************************/
// Car Mades
// Route::apiResource('car-mades', 'CarMadeApiController');
Route::get('car-types', 'CartypeApiController@index')->name('car-types.index');
Route::post('car-types', 'CartypeApiController@store')->name('car-types.store');
Route::post('car-types/{id}', 'CartypeApiController@update')->name('car-types.update');
Route::delete('car-types/{carType}', 'CartypeApiController@destroy')->name('car-types.destroy');
Route::get('car-types/{carType}', 'CartypeApiController@show')->name('car-types.show');
     // start search car mades with name
Route::post('car-types/search/name', 'CartypeApiController@search_with_name')
     ->name('car-types.search_with_name');
Route::post('cartypes/mass/delete', 'CartypeApiController@mass_delete')
        ->name('car-types.mass_delete');
/*********************************************************************/


/*********************************************************************/
    // Car Models
    // Route::apiResource('car-models', 'CarModelApiController');
Route::get('car-models', 'CarModelApiController@index')->name('car-models.index');
Route::post('car-models', 'CarModelApiController@store')->name('car-models.store');
Route::put('car-models/{id}', 'CarModelApiController@update')->name('car-models.update');
Route::delete('car-models/{carModel}', 'CarModelApiController@destroy')->name('car-models.destroy');
Route::get('car-models/{carModel}', 'CarModelApiController@show')->name('car-models.show');
     // start search car models with carmodel
Route::post('car-models/search/name', 'CarModelApiController@search_with_name')
     ->name('car-models.search_with_name');
/*********************************************************************/

/*********************************************************************/
    // Part Categories
Route::post('part-categories/media', 'PartCategoryApiController@storeMedia')->name('part-categories.storeMedia');
//    Route::apiResource('part-categories', 'PartCategoryApiController');
Route::get('part-categories', 'PartCategoryApiController@index')->name('part-categories.index');
Route::post('part-categories', 'PartCategoryApiController@store')->name('part-categories.store');
Route::post('part-categories/{partCategory}', 'PartCategoryApiController@update')
     ->name('part-categories.update');
Route::delete('part-categories/{partCategory}', 'PartCategoryApiController@destroy')
     ->name('part-categories.destroy');
Route::get('part-categories/{partCategory}', 'PartCategoryApiController@show')
     ->name('part-categories.show');
    // start search part categories with name
Route::post('part/categories/search/name', 'PartCategoryApiController@search_with_name')
     ->name('part-categories.search_with_name');
/*********************************************************************/

/*********************************************************************/
// all Categories 27 sep

Route::get('allcategories', 'AllcategoryIndexDashboardApiController@index')->name('allcategories.index');
Route::get('allcategories/details/{id}', 'AllcategoryDetailsDashboardApiController@index_details')->name('allcategories.index_details');

Route::post('allcategories', 'AllcategoryApiController@store')->name('allcategories.store');
Route::post('allcategories/{allcategory}', 'AllcategoryApiController@update')
     ->name('allcategories.update');
Route::delete('allcategories/{allcategory}', 'AllcategoryApiController@destroy')
     ->name('allcategories.destroy');
Route::get('allcategories/{allcategory}', 'AllcategoryApiController@show')
     ->name('allcategories.show');

Route::get('allcategories/list/{id}', 'AllcategoryApiController@list_all')
     ->name('allcategories.list_all');

Route::get('allcategories/navbarlist/{id}', 'AllcategoryNavbarDashboardApiController@navbar_list_all')
     ->name('allcategories.navbar_list_all');

Route::post('allcategories/mark/navbar', 'AllcategoryNavbarDashboardApiController@mark_navbar')
     ->name('allcategories.mark_navbar');

    // start search part categories with name
Route::get('allcategories/search/name', 'AllcategoryApiSearchDashboardController@search_with_name')
     ->name('allcategories.search_with_name');

Route::post('allcategoriesparent', 'AllcategoryApiController@parent')->name('allcategories.parent');

Route::get('allcategories/nested/list', 'AllcategoryApiController@nested_list_all')
     ->name('allcategories.nested_list_all');

// mass delete part categories
Route::post('allcategories/mass/delete', 'AllcategoryApiController@mass_delete')
        ->name('allcategories.mass_delete');

/*********************************************************************/

/*********************************************************************/
// Car Years
// Route::apiResource('car-years', 'CarYearApiController');
Route::get('car-years', 'CarYearApiController@index')->name('car-years.index');
Route::post('car-years', 'CarYearApiController@store')->name('car-years.store');
Route::put('car-years/{id}', 'CarYearApiController@update')->name('car-years.update');
Route::delete('car-years/{carYear}', 'CarYearApiController@destroy')->name('car-years.destroy');
Route::get('car-years/{carYear}', 'CarYearApiController@show')->name('car-years.show');
     // start search car years with year
Route::post('car-years/search/name', 'CarYearApiController@search_with_name')
     ->name('car-years.search_with_name');
Route::post('logos', 'CarYearApiController@logos')
     ->name('car-years.logos');

/*********************************************************************/

/*********************************************************************/
// Stores
Route::get('stores', 'StoresApiController@index')->name('stores.index');
Route::post('add/stores', 'StoresApiController@add_store')->name('stores.add_store');
Route::post('update/stores/{store}', 'StoresApiController@update')->name('stores.update');
Route::delete('stores/{store}', 'StoresApiController@destroy')->name('stores.destroy');
Route::get('stores/{store}', 'StoresApiController@show')->name('stores.show');
     // start search stores with year
Route::post('stores/search/name', 'StoresApiController@search_with_name')
     ->name('stores.search_with_name');
/*********************************************************************/


/*********************************************************************/
// manufacturers Crud
Route::get('manufacturers', 'Manufacturer\ManufacturerApiController@index')
    ->name('manufacturers.index');
Route::post('add/manufacturers', 'Manufacturer\ManufacturerApiController@add_manufacturers')
    ->name('manufacturers.add_manufacturers');
Route::post('update/manufacturers/{id}', 'Manufacturer\ManufacturerApiController@update')
    ->name('manufacturers.update');
Route::delete('manufacturers/{id}', 'Manufacturer\ManufacturerApiController@destroy')
    ->name('manufacturers.destroy');
Route::get('manufacturers/{id}', 'Manufacturer\ManufacturerApiController@show')
    ->name('manufacturers.show');
     // start search manufacturers with year
Route::post('manufacturers/search/name', 'Manufacturer\ManufacturerApiController@search_with_name')
     ->name('manufacturers.search_with_name');
// mass delete manufacturers
Route::post('manufacturers/mass/delete', 'Manufacturer\ManufacturerApiController@mass_delete')
        ->name('manufacturers.mass_delete');
/*********************************************************************/


/*********************************************************************/
// Origin Countries Crud
Route::get('origin/countries', 'OriginCountry\ProdcountryApiController@index')
    ->name('origin_countries.index');
Route::post('add/origin/countries', 'OriginCountry\ProdcountryApiController@add_origin_countries')
    ->name('origin_countries.add_store');
Route::post('update/origin/countries/{id}', 'OriginCountry\ProdcountryApiController@update')
    ->name('origin_countries.update');
Route::delete('origin/countries/{id}', 'OriginCountry\ProdcountryApiController@destroy')
    ->name('origin_countries.destroy');
Route::get('origin/countries/{id}', 'OriginCountry\ProdcountryApiController@show')
    ->name('origin_countries.show');
     // start search origin_countries with name
Route::post('origin/countries/search/name', 'OriginCountry\ProdcountryApiController@search_with_name')
    ->name('origin_countries.search_with_name');
// mass delete manufacturers
Route::post('origin/countries/mass/delete', 'OriginCountry\ProdcountryApiController@mass_delete')
        ->name('origin_countries.mass_delete');
/*********************************************************************/

/*********************************************************************/
// help center
Route::get('FAQs', 'HelpCenterApiController@index')->name('help_center.index');
Route::post('add/question', 'HelpCenterApiController@add_question')
    ->name('help_center.add_question');
Route::post('update/question/{question}', 'HelpCenterApiController@update_question')
    ->name('help_center.update_question');
Route::delete('delete/question/{question}', 'HelpCenterApiController@destroy_question')
    ->name('help_center.destroy_question');
Route::get('show/question/{question}', 'HelpCenterApiController@show_question')
    ->name('help_center.show_question');
     // start search questions with year
Route::post('questions/search', 'HelpCenterApiController@search_with_name')
    ->name('help_center.search_with_name');
/*********************************************************************/

/*********************************************************************/
    // Add Vendors
Route::post('add-vendors/media', 'AddVendorApiController@storeMedia')
     ->name('add-vendors.storeMedia');
Route::post('add-vendors/add/products', 'AddVendorApiController@add_products')
     ->name('add-vendors.addProducts');
Route::get('add-vendors/get/products', 'AddVendorApiController@get_vendor_products')
     ->name('add-vendors.getProducts');
Route::get('add-vendors/get/types', 'AddVendorApiController@get_vendor_types')
     ->name('add-vendors.getTypes');
Route::post('add-vendors/get/userid_id', 'AddVendorApiController@get_vendor_userid_id')
     ->name('add-vendors.getUserids');
// search add vendors with name
Route::post('add-vendors/search/name', 'AddVendorApiController@search_with_name')
     ->name('add-vendors.search_with_name');

// search add vendors with name
Route::post('add-vendors/edit/list/{id}', 'AddVendorApiController@edit_list')
     ->name('add-vendors.edit_list');


// admin approve order
Route::post('admin/approve/vendor', 'AddVendorApiController@approve_vendor')
     ->name('add-vendors.approve_vendor');

// admin decline order
Route::post('admin/decline/vendor', 'AddVendorApiController@decline_vendor')
     ->name('add-vendors.decline_vendor');

// admin reject order
Route::post('admin/reject/vendor', 'AddVendorApiController@reject_vendor')
     ->name('add-vendors.reject_vendor');

Route::get('reject/list', 'AddVendorApiController@fileds_list')
     ->name('add-vendors.fileds_list');

//    Route::apiResource('add-vendors', 'AddVendorApiController');
Route::get('add-vendors', 'AddVendorApiController@index')->name('add-vendors.index');
Route::get('count/pending/vendors', 'AddVendorApiController@count_pending_vendors')->name('add-vendors.count_pending_vendors');
Route::post('add-vendors', 'AddVendorApiController@store')->name('add-vendors.store');
Route::post('add-vendors/{addVendor}', 'AddVendorApiController@update')->name('add-vendors.update');
Route::delete('add-vendors/{addVendor}', 'AddVendorApiController@destroy')
     ->name('add-vendors.destroy');
Route::get('add-vendors/{addVendor}', 'AddVendorApiController@show')
     ->name('add-vendors.show');
/************************************************************************************/

/************************************************************************************/
// mass delete section

// mass delete helpcenters
Route::post('questions/mass/delete', 'HelpCenterApiController@mass_delete')
        ->name('help_center.mass_delete');

     // mass delete products
Route::post('products/mass/delete', 'ProductApiController@mass_delete')
        ->name('products.mass_delete');
/*   __________________________________________________________________________  */

 // mass delete categories
Route::post('categories/mass/delete', 'ProductCategoryApiController@mass_delete')
        ->name('categories.mass_delete');
/*   __________________________________________________________________________  */

 // mass delete part categories
Route::post('part-categories/mass/delete', 'PartCategoryApiController@mass_delete')
        ->name('part-categories.mass_delete');
/*   __________________________________________________________________________  */

 // mass delete product tags
Route::post('product-tags/mass/delete', 'ProductTagApiController@mass_delete')
        ->name('product-tags.mass_delete');
/*   __________________________________________________________________________  */

 // mass delete car mades
Route::post('car-mades/mass/delete', 'CarMadeApiController@mass_delete')
        ->name('car-mades.mass_delete');
/*   __________________________________________________________________________  */

 // mass delete car models
Route::post('car-models/mass/delete', 'CarModelApiController@mass_delete')
        ->name('car-models.mass_delete');
/*   __________________________________________________________________________  */

// mass delete car years
Route::post('car-years/mass/delete', 'CarYearApiController@mass_delete')
        ->name('car-years.mass_delete');
/*   __________________________________________________________________________  */

// mass delete add vendors
Route::post('add-vendors/mass/delete', 'AddVendorApiController@mass_delete')
        ->name('add-vendors.mass_delete');
/*   __________________________________________________________________________  */

// mass delete users
Route::post('users/mass/delete', 'UsersApiController@mass_delete')
        ->name('users.mass_delete');
/*   __________________________________________________________________________  */

// mass delete roles
Route::post('roles/mass/delete', 'RolesApiController@mass_delete')
        ->name('roles.mass_delete');

/*   __________________________________________________________________________  */

// mass delete store
Route::post('stores/mass/delete', 'StoresApiController@mass_delete')
        ->name('stores.mass_delete');

/************************************************************************************/

/************************************************************************************/
// list all indexes to select from dropdownlist 

/*   __________________________________________________________________________  */
// prodcountries list
Route::get('prodcountries/list', 'OriginCountry\ProdcountryApiController@list_all')
        ->name('prodcountries.list_all');
/*   __________________________________________________________________________  */

/*   __________________________________________________________________________  */
// prodcountries list
Route::get('ads/positions/list', 'AdPositionsApiController@list_all')
        ->name('ad-positions.list_all');
/*   __________________________________________________________________________  */

// manufacturer list
Route::get('manufacturer/list', 'Manufacturer\ManufacturerApiController@list_all')
        ->name('manufacturer.list_all');
/*   __________________________________________________________________________  */
// ticket/categorieslist list
Route::get('ticket/categorieslist', 'TicketCategoryListApiController@list_all')
        ->name('ticketcategories.list_all');

Route::get('ticket/priority/list', 'TicketCategoryListApiController@list_priority')
        ->name('ticketcategories.list_priority');
/*   __________________________________________________________________________  */
// categories list
Route::get('categorieslist', 'ProductCategoryApiController@list_all')
        ->name('categories.list_all');

Route::get('categorieslist/{id}', 'ProductCategoryApiController@list_all_maincategory')
        ->name('categories.list_all');
/*   __________________________________________________________________________  */
 //  part categories
Route::get('part-categorieslist/{id}', 'PartCategoryApiController@list_all')
        ->name('part-categories.list_all');
/*   __________________________________________________________________________  */

/*   __________________________________________________________________________  */
 //  part categories
Route::get('part-categorieslist', 'PartCategoryApiController@list_all_pure')
        ->name('part-categories.list_all_pure');
/*   __________________________________________________________________________  */


 //  part categories
Route::get('cartypes/list', 'CartypeApiController@list_all')
        ->name('car-types.list_all');
/*   __________________________________________________________________________  */

 //  product tags
Route::get('product-tagslist', 'ProductTagApiController@list_all')
        ->name('product-tags.list_all');
/*   __________________________________________________________________________  */

/*   __________________________________________________________________________  */

 //  product tags
Route::get('vendors-list', 'AddVendorApiController@list_all')
        ->name('vendors.list_all');
/*   __________________________________________________________________________  */

/*   __________________________________________________________________________  */

 //  product tags
Route::get('products-list', 'ProductApiController@list_all')
        ->name('products.list_all');

Route::get('transmissions/list', 'Search\ProductSelectSearchListApiController@list_all_transmissions')
    ->name('UserSearchProds.list_all_transmissions');
/*   __________________________________________________________________________  */

 //  car mades
Route::get('car-madeslist', 'CarMadeApiController@list_all')
        ->name('car-mades.list_all');

Route::get('cartype/madeslist/{id}', 'CarMadeApiController@list_all_related')
        ->name('car-mades.list_all_related');
/*   __________________________________________________________________________  */

/*   __________________________________________________________________________  */
 //  transmissions
Route::get('transmissions-list', 'TransmissionApiController@list_all')
        ->name('transmissions.list_all');
/*   __________________________________________________________________________  */

 //  car models
Route::get('car-modelslist/{id}', 'CarModelApiController@list_all')
        ->name('car-models.list_all');

/*Route::get('car-models/list/all', 'CarModelApiController@get_all')
        ->name('car-models.get_all');*/
/*   __________________________________________________________________________  */
//  car years
Route::get('car-yearslist', 'CarYearApiController@list_all')
        ->name('car-years.list_all');
/*   __________________________________________________________________________  */

//  roles
Route::get('roleslist', 'RolesApiController@list_all')
        ->name('roles.list_all');
/*   __________________________________________________________________________  */

//  roles
Route::get('product/types/list', 'ProductTypeApiController@list_all')
        ->name('product_types.list_all');
/*   __________________________________________________________________________  */

//  Permissions
Route::get('permissionslist', 'PermissionsApiController@list_all')
        ->name('permissions.list_all');

/*   __________________________________________________________________________  */

//  stores
Route::get('storeslist', 'StoresApiController@list_all')
        ->name('stores.list_all');

/************************************************************************************/

/************************************************************************************/

/*   _______________________________________________________________________________________  */
// admin orders section 
Route::post('admin/show/vendor/orders', 'AdminOrdersApiController@access_specific_vendor_orders')
    ->name('adminOrders.access_specific_vendor_orders');

// wholesale orders
Route::get('admin/show/wholesale/orders', 'AdminWholesaleOrdersApiController@wholesale_orders')
    ->name('adminOrders.wholesale_orders');

Route::get('admin/search/wholesale/orders', 'AdminWholesaleOrdersApiController@search_wholesale_orders')
    ->name('adminOrders.search_wholesale_orders');

Route::get('admin/show/wholesale/invoices', 'AdminWholesaleOrdersApiController@wholesale_invoices')
    ->name('adminOrders.wholesale_invoices');

Route::get('admin/search/wholesale/invoices', 'AdminWholesaleOrdersApiController@search_wholesale_invoices')
    ->name('adminOrders.search_wholesale_invoices');

Route::get('admin/show/vendor/orders/{vendor}/{order}', 'AdminOrdersApiController@access_specific_vendor_specific_order')
    ->name('adminOrders.access_specific_vendor_specific_order');

Route::post('admin/search/vendor/orders', 'AdminOrdersApiController@search_with_name')
    ->name('adminOrders.search_with_name');

/*   _______________________________________________________________________________________  */

/*   _______________________________________________________________________________________  */
// admin invoices section 
Route::post('admin/show/vendor/invoices', 'AdminInvoicesApiController@access_specific_vendor_invoices')->name('adminInvoices.access_specific_vendor_invoices');

Route::get('admin/show/vendor/invoices/{vendor}/{invoice}', 'AdminInvoicesApiController@access_specific_vendor_specific_invoice')
   ->name('adminInvoices.access_specific_vendor_specific_invoice');

Route::post('admin/search/vendor/invoices', 'AdminInvoicesApiController@search_with_name')
    ->name('adminInvoices.search_with_name');

/*   _______________________________________________________________________________________  */

/************************************************************************************/
/*   ______________________________________________________________________________________  */
// advertisement system
Route::get('all/ads', 'Ads\AdApiController@index')->name('ads.index');
Route::get('show/ads/{id}', 'Ads\AdApiController@show')->name('ads.show');
Route::get('show/ads/position/{id}', 'Ads\AdApiController@show_position_ads')->name('ads.show_position_ads');
Route::get('dynamic/cartype/ads/{id}', 'Ads\AdApiController@dynamic_cartype_ads')->name('ads.dynamic_cartype_ads');
Route::post('add/ads', 'Ads\AdApiController@store')->name('ads.store');
Route::post('update/ads/{id}', 'Ads\AdApiController@update')->name('ads.update');
Route::delete('delete/ads/{id}', 'Ads\AdApiController@destroy')->name('ads.delete');
/*   ______________________________________________________________________________________  */
/************************************************************************************/


}); // end prefix api/v1/admin

// start prefix api/v1/vendor
/*Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'Api\V1\Vendor', 'middleware' => ['auth:sanctum']], function () {*/
Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'Api\V1\Vendor', 'middleware' => ['auth:api']], function () {

// start online shopping section

/*   home vendor profile data   */
Route::get('vendor/about/rare/products', 'HomeVendor\RareProductsApiController@rare_products')
    ->name('vendorProducts.rare_products');
/*   home vendor profile data   */


// make order api
Route::post('make/order', 'OrderApiController@make_order')->name('orders.make_order');

// user cancel order check logged id == order user_id 
Route::post('cancel/order', 'OrderApiController@cancel_order')->name('orders.cancel_order');

// vendor see need approved orders
Route::get('orders/need/approval', 'VendorOrdersApiController@orders_need_approval')
    ->name('vendorOrders.orders_need_approval');

// search orders need approval
Route::post('orders/need/approval/search', 'VendorSearchApprovalOrdersApiController@search_orders_need_approval')
    ->name('vendorOrders.search_orders_need_approval');
 
// vendor approve orders (orderid / 1 true)
Route::post('vendor/approve/orders', 'VendorOrdersApiController@approve_order')
    ->name('vendorOrders.approve_order');

// vendor cancel order
Route::post('vendor/cancel/order', 'VendorOrdersApiController@cancel_order')
    ->name('vendorOrders.cancel_order');

/* change to one tab */
Route::get('show/invoices', 'InvoicesAccessApiController@show_invoices')
    ->name('Invoices.show_invoices');

Route::get('show/invoices/{invoice}', 'InvoicesAccessApiController@show_specific_invoice')
    ->name('Invoices.show_specific_invoice');

// vendor access all his orders 
/*Route::get('show/orders', 'OrdersAccessApiController@show_orders')
    ->name('Orders.show_orders');*/

Route::post('show/orders', 'TotalOrdersAccessApiController@show_orders')
    ->name('Orders.show_total_orders');

// vendor access specific order
Route::get('show/orders/{order}', 'OrdersAccessApiController@show_specific_order')
    ->name('Orders.show_specific_order');

Route::post('orders/search/name', 'OrdersAccessApiController@search_with_name')
    ->name('Orders.search_with_name');

Route::post('invoices/search/name', 'InvoicesAccessApiController@search_with_name')
    ->name('Invoices.search_with_name');

/************************************************************************************/

/************************************************************************************/
// vendor add staff 
Route::post('vendor/add/staff', 'Staff\VendorStaffApiController@vendor_add_staff')
->name('vendorStaff.vendor_add_staff');
Route::post('vendor/approve/staff', 'Staff\VendorStaffApiController@vendor_approve_staff')
->name('vendorStaff.vendor_approve_staff');
Route::post('vendor/reject/reasons', 'Staff\VendorStaffApiController@vendor_reject_reasons')
->name('vendorStaff.vendor_reject_reasons');
Route::post('vendor/assign/staff/stores', 'Staff\VendorStaffApiController@vendor_assign_stores_staff')
->name('vendorStaff.vendor_assign_stores_staff');
/************************************************************************************/
// prod questions 
Route::post('vendor/answer/question', 'ProductQuestionApiController@vendor_answer_question')
->name('prodquestions.add');

Route::post('vendor/fetch/question', 'ProductQuestionApiController@vendor_fetch_questions')
->name('prodquestions.vendor_fetch_questions');

Route::post('vendor/fetch/specific/question/{id}', 'ProductQuestionApiController@vendor_fetch_specific_question')
->name('prodquestions.vendor_fetch_specific_question');

Route::get('search/prod/questions', 'ProductQuestionApiController@search_prod_questions')
->name('prodquestions.search_prod_questions');

Route::get('prod/questions/index', 'ProductQuestionApiController@all_questions_index')
->name('prodquestions.all_questions_index');

/************************************************************************************/

/************************************************************************************/
// tickets as admin or vendor 
Route::get('all/tickets', 'TicketApiController@index')->name('tickets.index');
Route::get('show/ticket/{ticket}', 'TicketApiController@show')->name('tickets.show');
Route::post('new/ticket', 'TicketApiController@store')->name('tickets.store');

Route::post('solved/ticket', 'TicketApiController@solved_ticket')->name('tickets.solved_ticket');
Route::post('to/admin/ticket', 'TicketApiController@to_admin')->name('tickets.to_admin');
// Route::post('close/ticket/{ticket}', 'TicketApiController@close')->name('tickets.close');
// Route::post('comment', 'CommentsController@postComment');
Route::post('search/tickets', 'TicketSearchApiController@search_with_name')
    ->name('tickets.search_with_name');
Route::post('vendor/answer/ticket', 'TicketApiController@vendor_answer_ticket_edit')
    ->name('tickets.vendor_answer_ticket');
Route::post('admin/answer/ticket', 'TicketApiController@admin_answer_ticket')
    ->name('tickets.admin_answer_ticket');
Route::get('specific/order/tickets/{id}', 'TicketSearchApiController@specific_order_tickets')
    ->name('tickets.specific_order_tickets');

/************************************************************************************/

/************************************************************************************/
Route::post('fetch/basic/report', 'BasicApiReportController@fetch_data_period')
    ->name('basicReport.fetch_data_period');

Route::post('fetch/advanced/report', 'AdvancedApiReportController@fetch_data_period')
    ->name('advancedReport.fetch_data_period');

/************************************************************************************/ 
/************************************************************************************/

});   // end  prefix api/v1/vendor

/************************************************************************************/

// start prefix api/v1/user without middleware
Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'Api\V1\User'], function () {

Route::get('ss', 'Products\HomeProductsApiController@ss')
    ->name('vendorOrders.ss'); 
/*   ____________________________________________________________________________  */
// forget password

Route::post('user/forget/password', 'Authweb\ForgetPasswordApiController@sendEmail')
    ->name('userOrders.forget_password');

Route::post('user/password/reset', 'Authweb\ResetPasswordApiController@passwordResetProcess')
    ->name('userOrders.reset');
    // forget password
/* ___________________________________________________________________________  */

//register user api
/*   _________________________________________________________________________  */
Route::post('user/register', 'Authweb\UserRegisterApiController@user_register')
    ->name('website.user_register');

Route::post('verify/email', 'Authweb\UserRegisterApiController@verify_email')
    ->name('website.verify_email');

Route::post('resend/verify/email', 'Authweb\UserRegisterApiController@resend_verify_email')
    ->name('website.resend_verify_email');
/*   ________________________________________________________________________  */
// register vendor api 1st step
/*__________________________________________________________________  */
Route::post('vendor/register', 'Authweb\VendorRegisterApiController@vendor_register')
    ->name('website.vendor_register');
/*   _________________________________________________________________  */

/*   __________________________________________________________________  */
Route::get('register/roles/list', 'Authweb\UserRegisterApiController@register_roles_list')->name('website.register_roles_list');
/*   _________________________________________________________________________  */

//login user api
/*   __________________________________________________________________________  */
Route::post('user/login', 'Authweb\UserLoginApiController@user_login')->name('website.user_login');//->middleware('web');
/*   ______________________________________________________________________  */
Route::post('token/login', 'Authweb\UserLoginApiController@token_login')->name('website.token_login');

// facebook login
Route::post('login/facebook', 'Authweb\SocialMediaLoginApiController@login_facebook');

}); // end prefix api/v1/user without middleware

// start prefix api/v1/user
//Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'Api\V1\User', 'middleware' => ['auth:api']], function () {

//Route::group(['middleware' => ['cors', 'json.response']], function () {


////////////////////////////////////// HomePage ////////////////////////////
Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'Api\V1\User'], function () {

/*  AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA  */ 

Route::get('category/filterations/{id}', 'HomeCategoryFilterationApiControler@category_filterations')->name('allcategories.category_filterations');

// separate controllers
Route::get('home/allcategories', 'HomeAllcategoryIndexApiControler@index')->name('allcategories.index');

Route::get('home/allcategories/details/{id}', 'HomeAllcategoryIndexDetailsApiControler@index_details')->name('allcategories.index_details');

Route::get('home/allcategories/navbars/{id}', 'HomeAllcategoryNavbarDetailsApiControler@navbar_details')->name('allcategories.navbar_details');

Route::get('allcategories/nested/list', 'HomeAllcategoryApiControler@nested_list_all')
     ->name('allcategories.nested_list_all');

Route::get('home/allcategories/products/{id}', 'HomeAllcategoryProductsApiControler@index_products')->name('allcategories.index_products');

Route::get('ahmed/allcategories/products/{id}', 'HomeAllcategoryProductsFilterationApiControler@index_products')->name('allcategories.index_products');


/*  AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA  */ 


// contact us page
Route::post('contact-us', 'ContactApiController@saveContact');

Route::get('prod/all/questions/{id}', 'ProductQuestionApiController@prod_all_questions')
->name('prodquestions.prod_all_questions');

/*   _____________________________________________________________________________  */

Route::get('user/search/products', 'Search\ProductSearchApiController@search_with_name_render')
    ->name('UserSearchProds.search_with_name_render');

Route::get('ahmed/search/products', 'Search\ProductSearchFilterationApiController@search_with_name_render')
    ->name('UserSearchProds.search_with_name_render');

Route::get('part/category/attributes/{id}', 'Search\PartCategoriesAttributesSearchApiController@part_categories_attributes_v2')
    ->name('UserSearchProds.part_categories_attributes');


/* Route::get('all/category/attributes/{id}', 'Search\PartCategoriesAttributesSearchApiController@part_categories_attributes_v2')
    ->name('UserSearchProds.part_categories_attributes'); */

/* ______________________________________________________________________________________  */
/*Route::post('user/select/products', 'Search\ProductSelectSearchApiController@select_products')
    ->name('UserSearchProds.select_products');*/
// issue csrf
Route::post('display/search/results', 'Search\ProductSelectSearchApiController@display_search_results')->name('UserSearchProds.display_search_results');

Route::get('display/search/results/mobile', 'Search\MobileProductSelectSearchApiController@display_search_results_mobile')->name('UserSearchProds.display_search_results_mobile');

Route::post('ahmed/display/search/results', 'Search\MobileProductSelectSearchFilterationApiController@display_search_results')->name('UserSearchProds.display_search_results');

Route::get('ahmed/display/search/results', 'Search\MobileProductSelectSearchFilterationApiController@display_search_results')->name('UserSearchProds.display_search_results_mobile');

//  car mades
Route::get('car/madeslist/filter/{id}', 'Search\ProductSelectSearchListApiController@list_all_car_mades_cartype')
    ->name('UserSearchProds.list_all_car_mades_cartype');

Route::get('car/madeslist', 'Search\ProductSelectSearchListApiController@list_all_car_mades')
    ->name('UserSearchProds.list_all_car_mades');
// modify api

Route::get('car/modelslist/{id}', 'Search\ProductSelectSearchListApiController@list_all_car_models')->name('UserSearchProds.list_all_car_models');

/*   __________________________________________________________________________  */

//  car types
Route::get('car/types/list', 'Search\ProductSelectSearchListApiController@list_all_car_types')->name('UserSearchProds.list_all_car_types');

Route::get('front/ads/positions/list', 'Search\ProductSelectSearchListApiController@list_all_ads_positions')->name('UserSearchProds.list_all_ads_positions');
/*   __________________________________________________________________________  */

Route::get('car/modelslist', 'Search\ProductSelectSearchListApiController@get_all_car_models')->name('UserSearchProds.get_all_car_models');

Route::get('car/yearslist', 'Search\ProductSelectSearchListApiController@list_all_car_years')
    ->name('UserSearchProds.list_all_car_years');

Route::get('transmissions/list', 'Search\ProductSelectSearchListApiController@list_all_transmissions')
    ->name('UserSearchProds.list_all_transmissions');

Route::get('fetch/vendors/list', 'Search\ProductSelectSearchListApiController@fetch_vendors_list')->name('UserSearchProds.fetch_vendors_list');

Route::get('home/main/categories', 'Search\ProductSelectSearchListApiController@home_main_categories')->name('UserSearchProds.home_main_categories');

Route::get('home/main/categories/nested', 'Search\ProductSelectSearchListApiController@home_main_categories_nested')->name('UserSearchProds.home_main_categories_nested');

Route::get('home/category/parts/{id}', 'Search\ProductSelectSearchListApiController@home_categories_parts')->name('UserSearchProds.home_categories_parts');

Route::get('category/fetch/parts/{id}', 'Search\ProductSelectSearchListApiController@category_fetch_parts')->name('UserSearchProds.category_fetch_parts');

/* Route::get('search/home/category/parts', 'Search\ProductSelectSearchListApiController@search_home_categories_parts')->name('UserSearchProds.search_home_categories_parts'); */

/* Route::get('search/tyres/category/parts', 'Search\ProductSelectSearchListEditApiController@search_home_categories_parts')->name('UserSearchProds.search_home_categories_parts'); */

Route::get('search/home/category/parts', 'Search\ProductSelectSearchListEditApiController@search_home_categories_parts')->name('UserSearchProds.search_home_categories_parts');

Route::get('search/ahmed/category/parts', 'Search\ProductSelectSearchListFilterationApiController@search_home_categories_parts')->name('UserSearchProds.search_home_categories_parts');

/*   ______________________________________________________________________________________  */
// all products
Route::get('home/all/products', 'Products\HomeProductsApiController@home_all_products')
    ->name('HomePage.home_all_products');

Route::get('home/faqs', 'ProductQuestionApiController@home_faqs')
    ->name('website.home_faqs');

/*Route::get('loop', 'Products\HomeProductsApiController@loop')
    ->name('HomePage.loop');*/

Route::get('home/vendor/products/{id}', 'Products\HomeVendorProductsApiController@home_vendor_products')->name('HomePage.home_vendor_products');

Route::get('ahmed/vendor/products/{id}', 'Products\HomeVendorProductsFilterationApiController@home_vendor_products')->name('HomePage.ahmed_vendor_products');

// one product
Route::get('home/show/product/{id}', 'Products\HomeProductsApiController@home_show_product')
    ->name('HomePage.home_show_product');
    // one product
Route::get('home/review/product/{id}', 'Products\HomeProductsApiController@home_review_product')->name('HomePage.home_review_product');
/*   ______________________________________________________________________________  */

/*   __________________________________________________________________________________  */
/* Route::post('user/advanced/search/products', 'Search\ProductSearchApiController@advanced_search_products')->name('UserSearchProds.advanced_search_products'); */
/*   _________________________________________________________________________________  */
 // stand here
/*   __________________________________________________________________________________  */
/* Route::get('fetch/categories/nested/part', 'Front\FetchMultipleCheckboxApiController@categories_nested_part')->name('Front.categories_nested_part'); */

Route::get('fetch/categories/nested/part', 'Front\AllcategoryFetchMultipleCheckboxApiController@categories_nested_part')->name('Front.allcategories_nested_part2');

// editedapi
Route::get('fetch/allcategories/nested/part', 'Front\AllcategoryFetchMultipleCheckboxApiController@categories_nested_part')->name('Front.allcategories_nested_part');
/*   _________________________________________________________________________________  */

/*   __________________________________________________________________________________  */
Route::get('fetch/categories/nested/part/{category_id}', 'Front\FetchMultipleCheckboxApiController@categories_nested_part_specific')->name('Front.categories_nested_part_specific');
/* 

/*   _______________________________________________________________________________  */
/*Route::post('site/new/products', 'Front\FetchProductsApiController@fetch_new_products')
    ->name('front.fetch_new_products');*/

Route::get('site/new/products', 'Front\FetchNewProductsApiController@fetch_new_products_render')
    ->name('front.fetch_new_products_render');

Route::get('ahmed/new/products', 'HomeAllcategoy\FetchNewProductsApiController@fetch_new_products_render')
    ->name('front.ahmed_new_products_render');
/*   _________________________________________________________________________ */

/*   _______________________________________________________________________________  */
// front ads slider
Route::get('site/ads', 'Front\FetchSiteAdsApiController@fetch_site_ads')
    ->name('front.fetch_site_ads');
Route::get('site/ads/{id}', 'Front\FetchSiteAdsApiController@show')
    ->name('front.show');
Route::get('site/car/type/ads/{car_type}', 'Front\FetchSiteAdsApiController@dynamic_cartype_ads')
    ->name('front.dynamic_cartype_ads');
Route::get('site/position/ads/{position}', 'Front\FetchSiteAdsApiController@show_position_ads')
    ->name('front.show_position_ads');
Route::get('site/ads/show/filter', 'Front\FetchSiteAdsApiController@show_cartype_platform_ads')
    ->name('front.show_cartype_platform_ads');
/*   ______________________________________________________________________________________  */

/*   ______________________________________________________________________________________  */
/* Route::post('site/checkbox/filter', 'Front\FetchMultipleCheckboxApiController@fetch_multiple_checkbox')
    ->name('front.fetch_multiple_checkbox');*/

Route::post('site/checkbox/filter', 'Front\FetchMultipleCheckboxEditApiController@fetch_multiple_checkbox')->name('front.fetch_multiple_checkbox');

Route::get('category/checkbox/filter', 'Front\FetchCategoryCheckboxEditApiController@fetch_multiple_checkbox')->name('front.fetch_multiple_checkbox');

Route::post('site/checkbox/filter/search', 'Front\FetchMultipleCheckboxSearchApiController@fetch_multiple_checkbox')->name('front.fetch_multiple_checkbox');

Route::get('site/checkbox/filter/mobile', 'Front\MobileFetchMultipleCheckboxEditApiController@fetch_multiple_checkbox_mobile')->name('front.fetch_multiple_checkbox_mobile');
/*   ______________________________________________________________________________________  */

/*   ______________________________________________________________________________________  */
/* Route::get('site/categories', 'Front\FetchCategoriesApiController@fetch_categories')
    ->name('front.fetch_categories'); */

Route::get('site/categories/{id}', 'Front\FetchCategoriesApiController@fetch_specific_categories')
    ->name('front.fetch_specific_categories');

Route::get('site/part/categories/{id}', 'Front\FetchPartCategoriesApiController@fetch_specific_part_categories')
    ->name('front.fetch_specific_part_categories');
/*   ______________________________________________________________________________________  */

/*   ______________________________________________________________________________________  */
Route::get('site/part/categories', 'Front\FetchCategoriesApiController@fetch_part_categories')
    ->name('front.fetch_part_categories');
/*   ______________________________________________________________________________________  */

/*   ______________________________________________________________________________________  */
Route::get('car/type/related/products/{id}', 'Front\CarRelatedApiController@fetch_car_type_products')
->name('front.fetch_car_type_products');
/*   ______________________________________________________________________________________  */


// all manufacturers for checkbox select filter section
/*   ______________________________________________________________________________________  */
Route::get('site/manufacturers/list', 'Front\FetchOriginsManufacturersApiListController@fetch_home_manufacturers')
    ->name('front.fetch_home_manufacturers');
/*   ______________________________________________________________________________________  */


// all origins for checkbox select filter section
/*   ______________________________________________________________________________________  */
Route::get('site/origins/list', 'Front\FetchOriginsManufacturersApiListController@fetch_home_origins')->name('front.fetch_home_origins');
/*   ______________________________________________________________________________________  */

/*   ______________________________________________________________________________________  */
Route::get('best/seller/products', 'Front\FetchProductsApiController@best_seller_products')
    ->name('front.best_seller_products');

Route::get('ahmed/best/seller/products', 'HomeAllcategoy\BestSellerProductsApiController@best_seller_products')
    ->name('front.ahmed_best_seller_products');

/*   _________________________________________________________________________________  */

/*   ____________________________________________________________________________  */
Route::get('mostly/viewed/products', 'Products\HomeProductsApiController@mostly_viewed_products')
    ->name('front.mostly_viewed_products');

Route::get('most/viewed/products', 'HomeAllcategoy\MostlyViewedProductsApiController@mostly_viewed_products')
    ->name('front.allcategory_mostly_viewed_products');

Route::get('recently/viewed/products', 'Products\HomeReccentlyViewedProductsApiController@recently_viewed_products')
    ->name('front.recently_viewed_products');
/*   ______________________________________________________________________________________  */

/*   ______________________________________________________________________________________  */
// payment ways list
Route::get('all/paymentways', 'Paymentway\PaymentwayApiController@paymentways_all')
    ->name('website.paymentways_all');
/*   ______________________________________________________________________________________  */

}); // end prefix api/v1/user
////////////////////////////////////// HomePage ////////////////////////////

// cart needs login 
/* Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'Api\V1\User', 'middleware' => ['auth:sanctum']], function () {*/

Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'Api\V1\User', 'middleware' => ['auth:api']], function () {

Route::get('vendor/saved/docs', 'Authweb\VendorRegisterApiController@vendor_saved_docs')
    ->name('website.vendor_saved_docs');

Route::get('vendor/saved/center', 'Authweb\VendorRegisterApiController@vendor_saved_center')
    ->name('website.vendor_saved_center');

/*****************************************************/
// register vendor api 2nd step
Route::post('vendor/upload/docs', 'Authweb\VendorUploadDoumentsApiController@vendor_upload_docs')
    ->name('website.vendor_upload_docs');
    // head center credentials
Route::post('vendor/add/head/center', 'Authweb\HeadCenterApiController@vendor_head_center')
    ->name('website.vendor_head_center');
/*****************************************************/
// check valid token
/*   ______________________________________________________________________________________  */
Route::post('check/valid/session', 'Authweb\UserLoginApiController@check_session')->name('website.check_session');
/*   ______________________________________________________________________________________  */

/*   ______________________________________________________________________________________  */
Route::post('user/show/orders', 'Orders\UserOrdersApiController@show_orders')
    ->name('userOrders.show_orders');

Route::post('user/show/orders/{order}', 'Orders\UserOrdersApiController@show_specific_order')
    ->name('userOrders.show_specific_order');

Route::get('user/profile/info', 'Profile\UserProfileApiController@user_profile_info')
    ->name('userOrders.user_profile_info');

Route::post('user/edit/profile', 'Profile\UserProfileApiController@edit_profile')
    ->name('userOrders.edit_profile');

Route::post('user/change/password', 'Profile\UserProfileApiController@change_password')
    ->name('userOrders.change_password');

Route::post('user/logout', 'Profile\UserProfileApiController@logout')
    ->name('userOrders.logout');

Route::post('user/account/summary', 'Profile\UserProfileApiController@account_summary')
    ->name('userOrders.account_summary');
/*   _____________________________________________________________________________  */
   
/*   __________________________________________________________________________  */
// user select payment way
Route::post('user/select/paymentway', 'Paymentway\PaymentwayApiController@user_select_paymentway_checkout')->name('website.user_select_paymentway_checkout');

Route::post('user/default/paymentway', 'Paymentway\PaymentwayApiController@user_select_paymentway')->name('website.user_select_paymentway');
/*   _____________________________________________________________________________  */

/*______________________________________________________________________________  */
// checkout
Route::post('user/checkout', 'Checkout\CheckoutApiController@user_checkout')->name('website.user_checkout');
/*   ____________________________________________________________________________  */

/*   ___________________________________________________________________________  */
// user shipping crud
Route::get('user/all/shippings', 'Shipping\UserShippingApiController@index')
    ->name('userShipping.index');
Route::get('user/get/default/shipping', 'Shipping\UserShippingApiController@get_default_shipping')
    ->name('userShipping.get_default_shipping');
Route::post('user/add/shipping', 'Shipping\UserShippingApiController@store')
    ->name('userShipping.store');
Route::post('user/update/shipping/{id}', 'Shipping\UserShippingApiController@update')
    ->name('userShipping.update');
Route::get('user/show/shipping/{id}', 'Shipping\UserShippingApiController@show')
    ->name('userShipping.show');
Route::delete('user/delete/shipping/{id}', 'Shipping\UserShippingApiController@destroy')
    ->name('userShipping.destroy');
Route::get('user/list/shippings', 'Shipping\UserShippingApiController@list_all')
    ->name('userShipping.list_all');
Route::post('user/select/shipping/{id}', 'Shipping\UserShippingApiController@select_shipping_address')
    ->name('userShipping.select_shipping_address');
Route::post('user/mark/default/shipping/{id}', 'Shipping\UserShippingApiController@mark_default_shipping_address')
    ->name('userShipping.mark_default_shipping_address');
/*   ______________________________________________________________________________________  */


    /// cart database after login
    /*   __________________________________________________________________________  */
    Route::post('add/to/cart', 'Cart\DatabaseCartApiController@addtocart')
            ->name('ahmedEdit.addtocart');

    Route::post('update/to/cart', 'Cart\DatabaseCartApiController@updatetocart')
            ->name('ahmedEdit.updatetocart');

    Route::post('show/cart', 'Cart\DatabaseCartApiController@get_cart')
            ->name('ahmedEdit.get_cart');

    Route::post('clear/cart', 'Cart\DatabaseCartApiController@clear_cart')->name('ahmedEdit.clear_cart');

    Route::post('delete/from/cart', 'Cart\DatabaseCartApiController@deletefromcart')->name('ahmedEdit.deletefromcart');
    /*   __________________________________________________________________________  */
    /// cart database after login

/*   ___________________________________________________________________  */
    // user wishlist after login
Route::get('user/get/wishlist', 'Wishlist\UserWishlistApiController@user_get_wishlist')
    ->name('userWishlist.user_get_wishlist');

Route::post('user/add/wishlist', 'Wishlist\UserWishlistApiController@user_add_wishlist')
    ->name('userWishlist.user_add_wishlist');

Route::post('user/removeitem/wishlist', 'Wishlist\UserWishlistApiController@user_remove_item_wishlist')
    ->name('userWishlist.user_remove_item_wishlist');

Route::post('user/empty/wishlist', 'Wishlist\UserWishlistApiController@user_empty_wishlist')
    ->name('userWishlist.user_empty_wishlist');
    // user wishlist
/*   ______________________________________________________________________________________  */


/*   ______________________________________________________________________________________  */
    // start user add favourite products after login
Route::get('user/favourite/products', 'Products\UserFavouriteProductsApiController@user_get_favourite_products')
    ->name('userFavouriteProducts.user_get_favourite_products');

Route::post('user/add/favourite/product', 'Products\UserFavouriteProductsApiController@user_add_favourite')
    ->name('userFavouriteProducts.user_add_favourite');

Route::post('user/remove/favourite/product', 'Products\UserFavouriteProductsApiController@user_remove_favourite')
    ->name('userFavouriteProducts.user_remove_favourite');

Route::post('user/empty/favourite/products', 'Products\UserFavouriteProductsApiController@user_empty_favourite')
    ->name('userFavouriteProducts.user_empty_favourite');
    // end user add favourite products after login
/*   ______________________________________________________________________________________  */

/*   ______________________________________________________________________________________  */
    // start user product views
Route::get('user/most/viewed/products', 'Products\UserMostViewedProductsApiController@user_viewed_prods')
    ->name('userViewedProducts.user_viewed_prods');

Route::post('user/view/product', 'Products\UserMostViewedProductsApiController@user_view_product')
    ->name('userViewedProducts.user_view_product');

    // end user add evaluation products after login
/*   ____________________________________________________________________  */

/* user add question on product */
  Route::post('user/add/prod/question', 'ProductReviews\ProductReviewApiController@prod_question_add')
    ->name('reviews.prod_question_add');
/* user add question on product */
/*   __________________________________________________________________________  */
// user product reviews
Route::get('user/all/reviews', 'ProductReviews\ProductReviewApiController@index')
    ->name('reviews.index');
Route::post('user/add/review', 'ProductReviews\ProductReviewApiController@store')
    ->name('reviews.store');
Route::post('user/update/review/{id}', 'ProductReviews\ProductReviewApiController@update')
    ->name('reviews.update');
Route::get('user/show/review/{id}', 'ProductReviews\ProductReviewApiController@show')
    ->name('reviews.show');
Route::delete('user/delete/review/{id}', 'ProductReviews\ProductReviewApiController@destroy')
    ->name('reviews.destroy');
/*   _________________________________________________________________________  */

/*   ____________________________________________________________________  */
    // start user add evaluation products after login
Route::get('user/evaluation/products', 'Products\UserProductsEvaluationApiController@user_get_evaluation_products')
    ->name('userEvaluationProducts.user_get_evaluation_products');

Route::post('user/add/evaluation/product', 'Products\UserProductsEvaluationApiController@user_add_evaluation')
    ->name('userEvaluationProducts.user_add_evaluation');

Route::post('user/get/evaluation/specific/product', 'Products\UserProductsEvaluationApiController@user_get_evaluation_specific_product')
    ->name('userEvaluationProducts.user_get_evaluation_specific_product');

    // end user add evaluation products after login
/*   ______________________________________________________________________________________  */

/*******************************************************************************************/
Route::post('vendor/day/month/filter', 'Vendor\DayMonthFilterApiController@vendor_month_days_filter')
    ->name('advancedReport.vendor_month_days_filter');
/*******************************************************************************************/

Route::post('user/select/products/add/favourite/car', 'Search\UserFavouriteCarsApiController@add_to_my_favourites')
    ->name('UserFavouriteCars.add_to_my_favourites');

Route::get('user/select/from/favourites/{id}', 'Search\UserFavouriteCarsApiController@select_from_favourites')
    ->name('UserFavouriteCars.select_from_favourites');

Route::post('remove/favourite/car', 'Search\UserFavouriteCarsApiController@remove_favourite_car')
    ->name('UserFavouriteCars.remove_favourite_car');

Route::get('show/favourite/cars', 'Search\UserFavouriteCarsApiController@show_favourite_cars')
    ->name('UserFavouriteCars.show_favourite_cars');
});

 // ...
// });
/************************************************************************************/