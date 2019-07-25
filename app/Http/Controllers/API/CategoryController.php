<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use App\User; 
use App\Models\Category; 
use Illuminate\Support\Facades\Auth; 
use Validator;
use DB;

class CategoryController extends Controller
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
        $userData = Category::orderBy('id', 'DESC')->get();
        
        if(count($userData)) {
            $this->responseStatusCode = $this->statusCodes->success;
            $response = api_create_response($userData, $this->successText, 'Category List.');

        }else {
            $this->responseStatusCode = $this->statusCodes->not_found;
            $response = api_create_response(2, $this->failureText, 'No Category Found.');
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
     * Store a newly created resource in category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
         $validator = Validator::make($request->all(), [
                'category_name' => 'required',
                'category_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);
        
        if ($validator->fails()) {
          
            $response = api_create_response($validator->errors(), $this->failureText, '');
            return response()->json($response, $this->statusCodes->bad_request);
        }

          $category = Category::where(['category_name' => $request->input('category_name')])->first();
           
        if (!empty($category->category_name)) {
          
            $response = api_create_response(2, $this->failureText, 'This Category allready exit.');
             return response()->json($response, $this->statusCodes->bad_request);
         }

           $data = Auth::user();

            if(!empty($data->id)){

                  $image = $request->file('category_image');
                  $input['imagename'] = time().'.'.$image->getClientOriginalExtension();
                  $destinationPath = public_path('/category_image');
                  $image->move($destinationPath, $input['imagename']);
                  $data = [
                             'category_name' => $request->input('category_name'),
                             'category_image'=> $input['imagename'],
                             'created_by'    => $data->id
                          ];
                  
                  $cat_response = Category::insertCategory($data);

                  if(!empty($cat_response)){
                     
                     $this->responseStatusCode = $this->statusCodes->success;
                     $response = api_create_response(2, $this->successText, 'Category Added Successfully.');

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
        //echo "Dfdfd";
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

       $category = Category::find($id);;
       
        if(!empty($category->id)) {
            $this->responseStatusCode = $this->statusCodes->success;
            $response = api_create_response($category, $this->successText, 'Category List.');

        }else {
            $this->responseStatusCode = $this->statusCodes->not_found;
            $response = api_create_response(2, $this->failureText, 'No Category Found.');
        }
        
        return response()->json($response, $this->responseStatusCode);
    }

    /**
     * Update category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
         $validator = Validator::make($request->all(), [
                'category_name' => 'required',
        ]);
        
        if ($validator->fails()) {
          
            $response = api_create_response($validator->errors(), $this->failureText, '');
            return response()->json($response, $this->statusCodes->bad_request);
        }
    
         $user = Auth::user();

          if(!empty($user->id)){

         $cat_name_check = Category::where(['category_name' => $request->category_name])
                                     ->whereNotIn('id', [$id])->get();

         
         if(count($cat_name_check) == 1){

                $response = api_create_response(2, $this->failureText, 'This category all ready exit');
                return response()->json($response, $this->statusCodes->bad_request);
         }else{
                $category = Category::find($id);

                if($request->file('category_image')){

                  $image = $request->file('category_image');
                  $input['imagename'] = time().'.'.$image->getClientOriginalExtension();
                  $destinationPath = public_path('/category_image');
                  $image->move($destinationPath, $input['imagename']);

                }else{

                    $input['imagename'] = $category->category_image;
                }

              $data = [
                     'category_name' => $request->input('category_name'),
                     'category_image'=> $input['imagename'],
                     'created_by'    => $user->id,
                     'updated_at'    => date('Y-m-d H:i:s')
                  ];

               $data = Category::where('id', $id)->update($data);
                
                 if(!empty($data)){
                         
                    $this->responseStatusCode = $this->statusCodes->success;
                    $response = api_create_response(2, $this->successText, 'Category Update Successfully.');

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
     * Remove category.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        
       $record = Category::find($id);

        if(empty($record)) {
            $response = api_create_response(2, $this->failureText, 'No Category Found.');
            return response()->json($response, $this->statusCodes->not_found);
        }

        if(!empty($record->id)){

            $image_path = public_path('/category_image/'.$record->category_image);
             @unlink($image_path);

            $record->delete($record->id);
             $this->responseStatusCode = $this->statusCodes->success;
            $response = api_create_response(2, $this->successText, 'Category Deleted Successfully.');
        
        }else{

             $this->responseStatusCode = $this->statusCodes->bad_request;
             $response = api_create_response(2, $this->failureText, 'Something went wrong.');
            
        }
         return response()->json($response, $this->responseStatusCode); 
    }

    public function list_category($id){

          $category = Category::find($id);
       
        if(!empty($category->id)) {
            $this->responseStatusCode = $this->statusCodes->success;
            $response = api_create_response($category, $this->successText, 'Category List.');

        }else {
            $this->responseStatusCode = $this->statusCodes->not_found;
            $response = api_create_response(2, $this->failureText, 'No Category Found.');
        }
        
        return response()->json($response, $this->responseStatusCode);

    }
}



