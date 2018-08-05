<?php
use think\Route;

Route::get('api/:version/banner/:id', 'api/:version.Banner/getBanner');

Route::get('api/:version/theme', 'api/:version.Theme/getSimpleList');
Route::get('api/:version/theme/:id', 'api/:version.Theme/getProducts');

//Route::get('api/:version/product/recent', 'api/:version.Product/getRecent');
//Route::get('api/:version/product/by_category', 'api/:version.Product/getAllInCategory');
//Route::get('api/:version/product/:id', 'api/:version.Product/getOne', [], ['id' => '\d+']);

//Route::group('api/:version/product', function() {
//    Route::get('recent', 'api/:version.Product/getRecent');
//    Route::get('by_category', 'api/:version.Product/getAllInCategory');
//    Route::get(':id', 'api/:version.Product/getOne', [], ['id' => '\d+']);
//});

Route::group('api/:version/product', [
    'recent' => ['api/:version.Product/getRecent'],
    'by_category' => ['api/:version.Product/getAllInCategory'],
    ':id' => ['api/:version.Product/getOne', [], ['id' => '\d+']]
],['method' => 'get']);

Route::get('api/:version/category/all', 'api/:version.Category/getAllCategories');

Route::post('api/:version/token/user', 'api/:version.Token/getToken');