<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;  
use Illuminate\Support\Facades\Auth; 
use Validator;
use DB;
use DateTime;
use File;
use App\Models\Blog;

class BlogController extends Controller
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
         $blogData = Blog::get();
        
        if(count($blogData)) {
            $this->responseStatusCode = $this->statusCodes->success;
            $response = api_create_response($blogData, $this->successText, 'Blogs List.');

        }else {
            $this->responseStatusCode = $this->statusCodes->not_found;
            $response = api_create_response(2, $this->failureText, 'No Blogs Found.');
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
            'image'         => 'required',
            'category_name' => 'required', 
            'title'         => 'required|unique:blogs', 
            'description'   => 'required', 
            
        ]);

        if ($validator->fails()) {
          
            $response = api_create_response($validator->errors(), $this->failureText, '');
            return response()->json($response, $this->statusCodes->bad_request);
        }

        $user = Auth::user();

         if(!empty($user->full_name)){

              $file = $request->file('image');   
              $destinationPath = 'blogs';
              $file->move($destinationPath,$file->getClientOriginalName());

                $blogs = new Blog();
                $blogs->author_name = $user->full_name;
                $blogs->image = $file->getClientOriginalName();
                $blogs->category_name = $request->input('category_name');
                $blogs->title = $request->input('title');
                $blogs->description = $request->input('description');
                $blogs->status = 'true';
                $blogs->save();

                if(!empty($blogs)){
                     
                     $this->responseStatusCode = $this->statusCodes->success;
                     $response = api_create_response(2, $this->successText, 'New Blog Added Successfully.');

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
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
