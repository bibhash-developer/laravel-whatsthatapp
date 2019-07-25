<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User; 
use App\Models\Application; 
use Illuminate\Support\Facades\Auth; 
use Validator;
use DB;

class ApplicateController extends Controller
{
    public $successStatus = 200;
    private $statusCodes, $responseStatusCode, $successText, $failureText;
    public function __construct() {
        $this->statusCodes = config('api.status_codes');
        $this->tokenName = config('api.TOKEN_NAME');
        $this->successText = config('api.SUCCESS_TEXT');
        $this->failureText = config('api.FAILURE_TEXT');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $appliData = Application::join('category', 'application.category_id', '=', 'category.id')
                                  ->join('users',   'application.user_id',     '=', 'users.id')
                                  ->orderBy('id', 'DESC')
                                  ->select('application.*', 'category.category_name', 'users.full_name')->get();
        
        if(count($appliData)) {
            $this->responseStatusCode = $this->statusCodes->success;
            $response = api_create_response($appliData, $this->successText, 'application List.');

        }else {
            $this->responseStatusCode = $this->statusCodes->not_found;
            $response = api_create_response(2, $this->failureText, 'No application Found.');
        }
        
        return response()->json($response, $this->responseStatusCode);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
         $validator = Validator::make($request->all(), [
                'category_id' => 'required',
                'app_title'   => 'required|unique:application,app_title',
                'app_icon'    => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'affiliate_link'   => 'required',
                'description'   => 'required',
                'video_link'   => 'required',
                'app_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'download_link'   => 'required'
        ]);
        
        if ($validator->fails()) {
          
            $response = api_create_response($validator->errors(), $this->failureText, '');
            return response()->json($response, $this->statusCodes->bad_request);
        }
          $user = Auth::user();

          if(!empty($user->id)){
                   
                   // image for app icon.
                  $image = $request->file('app_icon');
                  $input['image_icon'] = time().'.'.$image->getClientOriginalExtension();
                  $destinationPath = public_path('/app_icon');
                  $image->move($destinationPath, $input['image_icon']);

                     // image for app image.
                  $image = $request->file('app_image');
                  $input['app_image'] = time().'.'.$image->getClientOriginalExtension();
                  $destinationPath = public_path('/app_image');
                  $image->move($destinationPath, $input['app_image']);



                  $data = [
                             'user_id'       => $user->id,
                             'category_id'   => $request->input('category_id'),
                             'app_title'     => $request->input('app_title'),
                             'app_icon'      =>  $input['image_icon'],
                             'affiliate_link' => $request->input('affiliate_link'),
                             'description'   => $request->input('description'),
                             'video_link'    => $request->input('video_link'),
                             'app_image'     => $input['app_image'],
                             'download_link' => $request->input('download_link'),
                          ];
                  
                  $appli_response = Application::insertApplicate($data);

                  if(!empty($appli_response)){
                     
                     $this->responseStatusCode = $this->statusCodes->success;
                     $response = api_create_response(2, $this->successText, 'Application Added Successfully.');

                  }else{
                    $this->responseStatusCode = $this->statusCodes->bad_request;
                    $response = api_create_response(2, $this->failureText, 'Something went wrong.');

                  }     

            }else{
                 $this->responseStatusCode = $this->statusCodes->bad_request;
                 $response = api_create_response(2, $this->failureText, 'Please enter valid credentials.');
            }

           return response()->json($response, $this->responseStatusCode); 
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
         $category = Application::find($id);
         $application = Application::join('category', 'application.category_id', '=', 'category.id')
                                  ->join('users',     'application.user_id',     '=', 'users.id')
                                  ->where('user_id', $id)
                                  ->orderBy('id', 'DESC')
                                  ->select('application.*', 'category.category_name', 'users.full_name')->get();
       
        if(!empty($application[0]->id)) {
            $this->responseStatusCode = $this->statusCodes->success;
            $response = api_create_response($application, $this->successText, 'Application List.');

        }else {
            $this->responseStatusCode = $this->statusCodes->not_found;
            $response = api_create_response(2, $this->failureText, 'No Application Found.');
        }
        
        return response()->json($response, $this->responseStatusCode);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
         $validator = Validator::make($request->all(), [
                'category_id' => 'required',
                'app_title'   => 'required',
                'affiliate_link'   => 'required',
                'description'   => 'required',
                'video_link'   => 'required',
                'download_link'   => 'required',
        ]);
        
        if ($validator->fails()) {
          
            $response = api_create_response($validator->errors(), $this->failureText, '');
            return response()->json($response, $this->statusCodes->bad_request);
        }

         $user = Auth::user();

          if(!empty($user->id)){

            $app_title_check = Application::where(['app_title' => $request->app_title])
                                          ->whereNotIn('id', [$id])->get();

            if(count($app_title_check) == 1){

                $response = api_create_response(2, $this->failureText, 'This application all ready exit');
                return response()->json($response, $this->statusCodes->bad_request);

             }else{

                    $application = Application::find($id);
                    
                   if(!empty($application->id)){                      

                     // image for app icon.
                   
                    if($request->file('app_icon')){

                      $image = $request->file('app_icon');
                      $input['image_icon'] = time().'.'.$image->getClientOriginalExtension();
                      $destinationPath = public_path('/app_icon');
                      $image->move($destinationPath, $input['image_icon']);

                    }else{

                        $input['image_icon'] = $application->app_icon;
                    }
                        // image for app image.
                 if($request->file('app_image')){

                      $image = $request->file('app_image');
                      $input['app_image'] = time().'.'.$image->getClientOriginalExtension();
                      $destinationPath = public_path('/app_image');
                      $image->move($destinationPath, $input['app_image']);

                    }else{

                        $input['app_image'] = $application->app_image;
                    }

                    $data = [
                             'user_id'       => $user->id,
                             'category_id'   => $request->input('category_id'),
                             'app_title'     => $request->input('app_title'),
                             'app_icon'      =>  $input['image_icon'],
                             'affiliate_link' => $request->input('affiliate_link'),
                             'description'   => $request->input('description'),
                             'video_link'    => $request->input('video_link'),
                             'app_image'     => $input['app_image'],
                             'download_link' => $request->input('download_link'),
                             'updated_by'    => $user->id,
                             'updated_at'    => date('Y-m-d H:i:s')
                          ];
                    $data = Application::where('id', $id)->update($data);

                 if(!empty($data)){
                         
                    $this->responseStatusCode = $this->statusCodes->success;
                    $response = api_create_response(2, $this->successText, 'Application Update Successfully.');

                 }else{
                    $this->responseStatusCode = $this->statusCodes->bad_request;
                    $response = api_create_response(2, $this->failureText, 'Something went wrong.');
                 }
                }else{
                    $this->responseStatusCode = $this->statusCodes->bad_request;
                    $response = api_create_response(2, $this->failureText, 'No Application Found.');
                }
            }
          }else{
                 $this->responseStatusCode = $this->statusCodes->bad_request;
                 $response = api_create_response(2, $this->failureText, 'Please enter valid credentials.');
          }
        return response()->json($response, $this->responseStatusCode);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
         $record = Application::find($id);

        if(empty($record)) {
            $response = api_create_response(2, $this->failureText, 'No Application Found.');
            return response()->json($response, $this->statusCodes->not_found);
        }

        if(!empty($record->id)){
            
             $icon_image_path = public_path('/app_icon/'.$record->app_icon);
             @unlink($icon_image_path);

             $app_image_path = public_path('/app_image/'.$record->app_image);
             @unlink($app_image_path);
             
            $record->delete($record->id);
             $this->responseStatusCode = $this->statusCodes->success;
            $response = api_create_response(2, $this->successText, 'Application Deleted Successfully.');
        
        }else{

             $this->responseStatusCode = $this->statusCodes->bad_request;
             $response = api_create_response(2, $this->failureText, 'Something went wrong.');
            
        }
         return response()->json($response, $this->responseStatusCode); 
    }

     /**
     * Application records show with category id.
     */

     public function application_list_with_cat($cat_id){

        $application = Application::where('category_id', $cat_id)
                                  ->orderBy('id', 'DESC')
                                  ->get();
       
        if(!empty($application[0]->id)) {
            $this->responseStatusCode = $this->statusCodes->success;
            $response = api_create_response($application, $this->successText, 'Application List.');

        }else {
            $this->responseStatusCode = $this->statusCodes->not_found;
            $response = api_create_response(2, $this->failureText, 'No Application Found.');
        }
        
        return response()->json($response, $this->responseStatusCode);
     }

     /**
     * Display the specified resource.
     *
     */
    public function edit_application($id)
    {
         $application = Application::find($id);
                
        if(!empty($application->id)) {
            $this->responseStatusCode = $this->statusCodes->success;
            $response = api_create_response($application, $this->successText, 'Application List.');

        }else {
            $this->responseStatusCode = $this->statusCodes->not_found;
            $response = api_create_response(2, $this->failureText, 'No Application Found.');
        }
        
        return response()->json($response, $this->responseStatusCode);
    }

    /**
     * Display for details page.
     *
     */

    public function application_details($id)
    {
         $application = Application::join('category', 'application.category_id', '=', 'category.id')
                                  ->join('users',     'application.user_id',     '=', 'users.id')
                                  ->where('application.id', $id)
                                  ->select('application.*', 'category.category_name', 'users.full_name')->get();
              
        if(!empty($application[0]->id)) {
            $this->responseStatusCode = $this->statusCodes->success;
            $response = api_create_response($application[0], $this->successText, 'Application List.');

        }else {
            $this->responseStatusCode = $this->statusCodes->not_found;
            $response = api_create_response(2, $this->failureText, 'No Application Found.');
        }
        
        return response()->json($response, $this->responseStatusCode);
    }

    /**
     * Display for autosuggestion.
     *
     */

    public function appli_search_auto($title)
    {
         $application = Application::where('app_title', 'like', '%' . $title . '%')->get();
             //echo "<pre>@@"; print_r($application); exit;
        if(!empty($application[0]->id)) {
            $this->responseStatusCode = $this->statusCodes->success;
            $response = api_create_response($application, $this->successText, 'Application List.');

        }else {
            $this->responseStatusCode = $this->statusCodes->not_found;
            $response = api_create_response(2, $this->failureText, 'No Application Found.');
        }
        
        return response()->json($response, $this->responseStatusCode);
    }
}
