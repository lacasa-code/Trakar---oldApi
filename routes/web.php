
<?php


Route::get('ahmed', function(){
    return view('Register.register');
});

Route::redirect('/', '/login');
Route::get('/home', function () {
    if (session('status')) {
        return redirect()->route('admin.home')->with('status', session('status'));
    }

    return redirect()->route('admin.home');
});

Auth::routes(['register' => false]);

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Admin', 'middleware' => ['auth']], function () {
    Route::get('/', 'HomeController@index')->name('home');

/******************************************************************************************/
    // Permissions
    Route::delete('permissions/destroy', 'PermissionsController@massDestroy')->name('permissions.massDestroy');
    Route::resource('permissions', 'PermissionsController');
/******************************************************************************************/

/******************************************************************************************/
    // Roles
    Route::delete('roles/destroy', 'RolesController@massDestroy')->name('roles.massDestroy');
    Route::resource('roles', 'RolesController');
/******************************************************************************************/

/******************************************************************************************/
    // Users
    Route::delete('users/destroy', 'UsersController@massDestroy')->name('users.massDestroy');
    Route::resource('users', 'UsersController');
/******************************************************************************************/

/******************************************************************************************/
    // Audit Logs
    Route::resource('audit-logs', 'AuditLogsController', ['except' => ['create', 'store', 'edit', 'update', 'destroy']]);
/******************************************************************************************/
    // Product Categories
    Route::delete('product-categories/destroy', 'ProductCategoryController@massDestroy')->name('product-categories.massDestroy');
    Route::post('product-categories/media', 'ProductCategoryController@storeMedia')->name('product-categories.storeMedia');
    Route::post('product-categories/ckmedia', 'ProductCategoryController@storeCKEditorImages')->name('product-categories.storeCKEditorImages');
    Route::post('product-categories/parse-csv-import', 'ProductCategoryController@parseCsvImport')->name('product-categories.parseCsvImport');
    Route::post('product-categories/process-csv-import', 'ProductCategoryController@processCsvImport')->name('product-categories.processCsvImport');
    
   // Route::resource('product-categories', 'ProductCategoryController');
Route::get('product-categories', 'ProductCategoryController@index')->name('product-categories.index');
Route::get('product-categories/create', 'ProductCategoryController@create')->name('product-categories.create');
Route::post('product-categories', 'ProductCategoryController@store')->name('product-categories.store');
Route::get('product-categories/{productCategory}/edit', 'ProductCategoryController@edit')->name('product-categories.edit');
Route::put('product-categories/{productCategory}', 'ProductCategoryController@update')->name('product-categories.update');
Route::delete('product-categories/{productCategory}', 'ProductCategoryController@destroy')->name('product-categories.destroy');
Route::get('product-categories/{productCategory}', 'ProductCategoryController@show')->name('product-categories.show');
/******************************************************************************************/

/******************************************************************************************/
    // Product Tags
    Route::delete('product-tags/destroy', 'ProductTagController@massDestroy')->name('product-tags.massDestroy');
    // Route::resource('product-tags', 'ProductTagController');
Route::get('product-tags', 'ProductTagController@index')->name('product-tags.index');
Route::get('product-tags/create', 'ProductTagController@create')->name('product-tags.create');
Route::post('product-tags', 'ProductTagController@store')->name('product-tags.store');
Route::get('product-tags/{productTag}/edit', 'ProductTagController@edit')->name('product-tags.edit');
Route::put('product-tags/{productTag}', 'ProductTagController@update')->name('product-tags.update');
Route::delete('product-tags/{productTag}', 'ProductTagController@destroy')->name('product-tags.destroy');
Route::get('product-tags/{productTag}', 'ProductTagController@show')->name('product-tags.show');
/******************************************************************************************/

/******************************************************************************************/
    // Products
    Route::delete('products/destroy', 'ProductController@massDestroy')->name('products.massDestroy');
    Route::post('products/media', 'ProductController@storeMedia')->name('products.storeMedia');
    Route::post('products/ckmedia', 'ProductController@storeCKEditorImages')->name('products.storeCKEditorImages');
    Route::post('products/parse-csv-import', 'ProductController@parseCsvImport')->name('products.parseCsvImport');
    Route::post('products/process-csv-import', 'ProductController@processCsvImport')->name('products.processCsvImport');
    Route::resource('products', 'ProductController');
/******************************************************************************************/

/******************************************************************************************/
    // Car Mades
    Route::delete('car-mades/destroy', 'CarMadeController@massDestroy')->name('car-mades.massDestroy');
    Route::post('car-mades/parse-csv-import', 'CarMadeController@parseCsvImport')->name('car-mades.parseCsvImport');
    Route::post('car-mades/process-csv-import', 'CarMadeController@processCsvImport')->name('car-mades.processCsvImport');
    //Route::resource('car-mades', 'CarMadeController');
Route::get('car-mades', 'CarMadeController@index')->name('car-mades.index');
Route::get('car-mades/create', 'CarMadeController@create')->name('car-mades.create');
Route::post('car-mades', 'CarMadeController@store')->name('car-mades.store');
Route::get('car-mades/{carMade}/edit', 'CarMadeController@edit')->name('car-mades.edit');
Route::put('car-mades/{carMade}', 'CarMadeController@update')->name('car-mades.update');
Route::delete('car-mades/{carMade}', 'CarMadeController@destroy')->name('car-mades.destroy');
Route::get('car-mades/{carMade}', 'CarMadeController@show')->name('car-mades.show');
/******************************************************************************************/

/******************************************************************************************/
    // Car Models
    Route::delete('car-models/destroy', 'CarModelController@massDestroy')->name('car-models.massDestroy');
    Route::post('car-models/parse-csv-import', 'CarModelController@parseCsvImport')->name('car-models.parseCsvImport');
    Route::post('car-models/process-csv-import', 'CarModelController@processCsvImport')->name('car-models.processCsvImport');
    //Route::resource('car-models', 'CarModelController');
Route::get('car-models', 'CarModelController@index')->name('car-models.index');
Route::get('car-models/create', 'CarModelController@create')->name('car-models.create');
Route::post('car-models', 'CarModelController@store')->name('car-models.store');
Route::get('car-models/{carModel}/edit', 'CarModelController@edit')->name('car-models.edit');
Route::put('car-models/{carModel}', 'CarModelController@update')->name('car-models.update');
Route::delete('car-models/{carModel}', 'CarModelController@destroy')->name('car-models.destroy');
Route::get('car-models/{carModel}', 'CarModelController@show')->name('car-models.show');
/******************************************************************************************/

/******************************************************************************************/
    // Part Categories
    Route::delete('part-categories/destroy', 'PartCategoryController@massDestroy')->name('part-categories.massDestroy');
    Route::post('part-categories/media', 'PartCategoryController@storeMedia')->name('part-categories.storeMedia');
    Route::post('part-categories/ckmedia', 'PartCategoryController@storeCKEditorImages')->name('part-categories.storeCKEditorImages');
    Route::post('part-categories/parse-csv-import', 'PartCategoryController@parseCsvImport')->name('part-categories.parseCsvImport');
    Route::post('part-categories/process-csv-import', 'PartCategoryController@processCsvImport')->name('part-categories.processCsvImport');
// Route::resource('part-categories', 'PartCategoryController');
Route::get('part-categories', 'PartCategoryController@index')->name('part-categories.index');
Route::get('part-categories/create', 'PartCategoryController@create')->name('part-categories.create');
Route::post('part-categories', 'PartCategoryController@store')->name('part-categories.store');
Route::get('part-categories/{partCategory}/edit', 'PartCategoryController@edit')->name('part-categories.edit');
Route::put('part-categories/{partCategory}', 'PartCategoryController@update')->name('part-categories.update');
Route::delete('part-categories/{partCategory}', 'PartCategoryController@destroy')->name('part-categories.destroy');
Route::get('part-categories/{partCategory}', 'PartCategoryController@show')->name('part-categories.show');
/******************************************************************************************/

/******************************************************************************************/
    // Car Years
    Route::delete('car-years/destroy', 'CarYearController@massDestroy')->name('car-years.massDestroy');
    Route::post('car-years/parse-csv-import', 'CarYearController@parseCsvImport')->name('car-years.parseCsvImport');
    Route::post('car-years/process-csv-import', 'CarYearController@processCsvImport')->name('car-years.processCsvImport');
//    Route::resource('car-years', 'CarYearController');
Route::get('car-years', 'CarYearController@index')->name('car-years.index');
Route::get('car-years/create', 'CarYearController@create')->name('car-years.create');
Route::post('car-years', 'CarYearController@store')->name('car-years.store');
Route::get('car-years/{carYear}/edit', 'CarYearController@edit')->name('car-years.edit');
Route::put('car-years/{carYear}', 'CarYearController@update')->name('car-years.update');
Route::delete('car-years/{carYear}', 'CarYearController@destroy')->name('car-years.destroy');
Route::get('car-years/{carYear}', 'CarYearController@show')->name('car-years.show');

/******************************************************************************************/

/******************************************************************************************/
    // Add Vendors
    Route::delete('add-vendors/destroy', 'AddVendorController@massDestroy')->name('add-vendors.massDestroy');
    Route::post('add-vendors/media', 'AddVendorController@storeMedia')->name('add-vendors.storeMedia');
    Route::post('add-vendors/ckmedia', 'AddVendorController@storeCKEditorImages')->name('add-vendors.storeCKEditorImages');
    Route::post('add-vendors/parse-csv-import', 'AddVendorController@parseCsvImport')->name('add-vendors.parseCsvImport');
    Route::post('add-vendors/process-csv-import', 'AddVendorController@processCsvImport')->name('add-vendors.processCsvImport');
    // Route::resource('add-vendors', 'AddVendorController');
Route::get('add-vendors', 'AddVendorController@index')->name('add-vendors.index');
Route::get('add-vendors/create', 'AddVendorController@create')->name('add-vendors.create');
Route::post('add-vendors', 'AddVendorController@store')->name('add-vendors.store');
Route::get('add-vendors/{addVendor}/edit', 'AddVendorController@edit')->name('add-vendors.edit');
Route::put('add-vendors/{addVendor}', 'AddVendorController@update')->name('add-vendors.update');
Route::delete('add-vendors/{addVendor}', 'AddVendorController@destroy')->name('add-vendors.destroy');
Route::get('add-vendors/{addVendor}', 'AddVendorController@show')->name('add-vendors.show');
/******************************************************************************************/

});
Route::group(['prefix' => 'profile', 'as' => 'profile.', 'namespace' => 'Auth', 'middleware' => ['auth']], function () {
// Change password
    if (file_exists(app_path('Http/Controllers/Auth/ChangePasswordController.php'))) {
        Route::get('password', 'ChangePasswordController@edit')->name('password.edit');
        Route::post('password', 'ChangePasswordController@update')->name('password.update');
        Route::post('profile', 'ChangePasswordController@updateProfile')->name('password.updateProfile');
        Route::post('profile/destroy', 'ChangePasswordController@destroy')->name('password.destroyProfile');
    }
});