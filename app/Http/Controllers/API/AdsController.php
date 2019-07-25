<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User; 
use App\Models\Ads; 
use Illuminate\Support\Facades\Auth; 
use Validator;
use DB;
use DateTime;
use File;

class AdsController extends Controller
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
         $user = Auth::user();

         if($user->user_type == 'admin'){

            $adsData = Ads::get();
        
                if(count($adsData)) {
                   
                   foreach($adsData as $value){

                    if(date('Y-m-d H:i:s') > $value->end_date){
                          
                        $data = Ads::where('id', $value->id)->update(['status' => 'false']);
                     }
                     
                   }
                   $this->responseStatusCode = $this->statusCodes->success;
                   $response = api_create_response($adsData, $this->successText, 'Ads List.');
                }else {
                    $this->responseStatusCode = $this->statusCodes->not_found;
                    $response = api_create_response(2, $this->failureText, 'No ads Found.');
                }

         }else{
                 $this->responseStatusCode = $this->statusCodes->bad_request;
                 $response = api_create_response(2, $this->failureText, 'Please enter valid credentials.');
         }
        return response()->json($response, $this->responseStatusCode);
    }

      /**
     * Display a listing without token.
     *
     * @return \Illuminate\Http\Response
     */
    public function list()
    {
        $adsData = Ads::where('status', 'true')->get();
    
              if(count($adsData)) {

                   $addRecord = array();
                   foreach($adsData as $value){

                    if(date('Y-m-d H:i:s') > $value->end_date){
                          
                        $data = Ads::where('id', $value->id)->update(['status' => 'false']);
                     }
                       $start_date   = explode(" ", $value->start_date);
                       $current_date = explode(" ", date('Y-m-d H:i:s'));
                        
                      if(($start_date[0] == $current_date[0]) || ($current_date[0] > $start_date[0])){
                          
                        $addRecord[] = $value;
                     }
                     
                   }
                   $this->responseStatusCode = $this->statusCodes->success;
                   $response = api_create_response($addRecord, $this->successText, 'Ads List.');
                }else {
                    $this->responseStatusCode = $this->statusCodes->not_found;
                    $response = api_create_response(2, $this->failureText, 'No ads Found.');
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
                'ads_position' => 'required',
                'start_date'   => 'required',
                'end_date'    => 'required',
                'status'       => 'required',
        ]);
        
        if ($validator->fails()) {
          
            $response = api_create_response($validator->errors(), $this->failureText, '');
            return response()->json($response, $this->statusCodes->bad_request);
        }

        $user = Auth::user();

         if($user->user_type == 'admin'){
           
                 // image for ads.
            if(!empty($request->input('image_url'))){

                $image_url   = $request->input('image_url');  

                $image_name  = 'null';
                
            }else{
                $image_url   = 'null';
            }

            if($request->file('image')){

                  $image      = $request->file('image');
                  $image_name = time().'.'.$image->getClientOriginalExtension();
                  $destinationPath = public_path('/ads_image');
                  $image->move($destinationPath, $image_name); 
            }

            if(!($request->input('image_url') || $request->file('image'))){

                 $this->responseStatusCode = $this->statusCodes->bad_request;
                 $response = api_create_response(2, $this->failureText, 'Please enter valid Image Or Image url.');
                 return response()->json($response, $this->responseStatusCode);
            }

                $data = [
                             'ads_position' => $request->input('ads_position'),
                             'image'        => $image_name,
                             'image_url'    => $image_url,
                             'links'        => $request->input('links'),
                             'start_date'   => $request->input('start_date'),
                             'end_date'     => $request->input('end_date'),
                             'status'       => $request->input('status')
                       ];
                      
                      if($request->input('status') == 'true'){

                             $ads_record = Ads::where('ads_position', $request->input('ads_position'))
                                               ->where('status',       $request->input('status'))->first();
                      }else{
                            $ads_record = 0;
                      }
            
       
                if(!empty($ads_record)) {

                    $this->responseStatusCode = $this->statusCodes->bad_request;
                    $response = api_create_response(2, $this->successText, 'This Ads position allready resurve.');
                    
                }else{
                        $ads_response = Ads::insertAds($data);

                        if(!empty($ads_response)){
                             
                             $this->responseStatusCode = $this->statusCodes->success;
                             $response = api_create_response(2, $this->successText, 'New Ads Added Successfully.');

                        }else{
                            $this->responseStatusCode = $this->statusCodes->bad_request;
                            $response = api_create_response(2, $this->failureText, 'Something went wrong.');

                        }  
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
         $ads_record = Ads::where('id', $id)->first();
         
        if(!empty($ads_record->id)) {

            $this->responseStatusCode = $this->statusCodes->success;
            $response = api_create_response($ads_record, $this->successText, 'Ads List.');

        }else {
            $this->responseStatusCode = $this->statusCodes->not_found;
            $response = api_create_response(2, $this->failureText, 'No Ads Found.');
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
                'ads_position' => 'required',
                'start_date'   => 'required',
                'end_date'    => 'required',
                'status'       => 'required',
        ]);
        
        if ($validator->fails()) {
          
            $response = api_create_response($validator->errors(), $this->failureText, '');
            return response()->json($response, $this->statusCodes->bad_request);
        }

        $user = Auth::user();

         if($user->user_type == 'admin'){

             $ads_record = Ads::where('id', $id)->first();
         
                if(!empty($ads_record->id)) {

                        // image for ads.
                    if(!empty($request->input('image_url'))){

                            $image_url   = $request->input('image_url');  

                        }else{
                            $image_url   = 'null';
                        }

                    if($request->file('image')){

                          $image      = $request->file('image');
                          $image_name = time().'.'.$image->getClientOriginalExtension();
                          $destinationPath = public_path('/ads_image');
                          $image->move($destinationPath, $image_name); 
                    }

                       if(!($request->file('image'))){
                        
                            if(!empty($ads_record->image)){

                                $image_name  = $ads_record->image;;
                            }else{
                                $image_name  = 'null';
                             }
                        }

                    $data = [
                             'ads_position' => $request->input('ads_position'),
                             'image'        => $image_name,
                             'image_url'    => $image_url,
                             'links'        => $request->input('links'),
                             'start_date'   => $request->input('start_date'),
                             'end_date'     => $request->input('end_date'),
                             'status'       => $request->input('status')
                       ];
                       
                       if($request->input('status') == 'true' && $ads_record->status != 'true'){
                           
                             $ads_record = Ads::where('ads_position', $request->input('ads_position'))
                                               ->where('status',       $request->input('status'))->first();
                            
                      }else{
                            $ads_record = 0;
                            
                      }
                          //check position with status.
            if(!empty($ads_record)) {

                    $this->responseStatusCode = $this->statusCodes->bad_request;
                    $response = api_create_response(2, $this->successText, 'This Ads position allready resurve.');
                    
             }else{
                
                       $data_response = Ads::where('id', $id)->update($data);

                           if(!empty($data_response)){
                                 
                                 $this->responseStatusCode = $this->statusCodes->success;
                                 $response = api_create_response(2, $this->successText, 'Ads Updated Successfully.');

                            }else{
                                $this->responseStatusCode = $this->statusCodes->bad_request;
                                $response = api_create_response(2, $this->failureText, 'Something went wrong.');

                            }  
                        } 
                      

                }else {
                    $this->responseStatusCode = $this->statusCodes->not_found;
                    $response = api_create_response(2, $this->failureText, 'No Ads Found.');
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
          $user = Auth::user();

         if($user->user_type == 'admin'){

            $record = Ads::find($id);

            if(empty($record)) {
                $response = api_create_response(2, $this->failureText, 'No Ads Found.');
                return response()->json($response, $this->statusCodes->not_found);
            }

            if(!empty($record->id)){
                   
                    $image_path = public_path('/ads_image/'.$record->image);
                    @unlink($image_path);
                   $record->delete($record->id);

                 $this->responseStatusCode = $this->statusCodes->success;
                $response = api_create_response(2, $this->successText, 'Ads Deleted Successfully.');
            
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
}
