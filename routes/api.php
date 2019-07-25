<?php

use Illuminate\Http\Request;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: *');
header('Access-Control-Allow-Headers: X-Requested-With, content-type, X-Token, x-token, authorization');

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/

Route::post('login',      'API\UserController@login');
Route::post('register',    'API\UserController@register');
//Route::get('user/logout',  'API\UserController@logout');
Route::post('forgot-password', 'API\UserController@forgot_password');
Route::post('reset-password',  'API\UserController@forgot_password1');

Route::resource('category-list',     'API\CategoryController');

   /* ------------------  Application with Category  ---------------- */

 Route::get('application-list-with-category/{cat_id}',  'API\ApplicateController@application_list_with_cat');
 Route::get('application-details/{id}',  'API\ApplicateController@application_details');
 Route::get('autosuggestion/{title}',  'API\ApplicateController@appli_search_auto');

 /* ------------------  Show Ads section  ---------------- */

Route::get('list',        'API\AdsController@list');

 /* ------------------  Show Category section  ---------------- */

Route::get('categorylist-website/{id}', 'API\CategoryController@list_category');

Route::group(['middleware' => 'auth:api'], function(){

Route::get('user-listing',           'API\UserController@details');

     /* ------------------  category  ---------------- */

Route::post('update-category/{id}',  'API\CategoryController@update');
Route::get('edit-category/{id}',     'API\CategoryController@edit');
//Route::get('delete-category/{id}', 'API\CategoryController@destroy');

Route::resource('category',           'API\CategoryController');

     /* ------------------  application  ---------------- */

 Route::resource('application',        'API\ApplicateController');
 Route::post('update-application/{id}',  'API\ApplicateController@update');
 Route::get('edit-application/{id}',     'API\ApplicateController@edit_application');

  /* ------------------  Ads section  ---------------- */

  Route::resource('ads',           'API\AdsController');
  Route::post('update-ads/{id}',   'API\AdsController@update');
  Route::get('status/{id}',        'API\AdsController@staupdate');

/* ------------------  Blogs section  ---------------- */

  Route::resource('blog',           'API\BlogController');

 /* ------------------  User Logout   ---------------- */

  Route::get('user/logout',  'API\UserController@logout');

});