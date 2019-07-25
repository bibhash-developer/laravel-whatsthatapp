<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use App\User; 
use Illuminate\Support\Facades\Auth; 
use Illuminate\Validation\Rule;
use Validator;
use DB;

use Hash;
//use Mail;
use Illuminate\Support\Facades\Mail;

use App\Mail\UserForgotPassword;
/*use App\Mail\UserResetPassword;
use App\Mail\UserActivationLink;*/

class UserController extends Controller 
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
     * login api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function login(Request $request){ 

    	 $validator = Validator::make($request->all(), [
                    'email' => 'required|email',
                    'password' => 'required',
        ]);
        
        if ($validator->fails()) {
          
            $response = api_create_response($validator->errors(), $this->failureText, '');
            return response()->json($response, $this->statusCodes->bad_request);
        }

        if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){ 
            $user = Auth::user(); 
          
            $user['user_id']    = $user->id;
            $user['user_type']  = $user->user_type;
            $user['full_name']  = $user->full_name;
            $user['email']      = $user->email;
            $user['created_at'] = $user->created_at;
            $user['updated_at'] = $user->updated_at;
            $user['token'] =  $user->createToken('MyApp')-> accessToken; 

             $this->responseStatusCode = $this->statusCodes->success;
             $response = api_create_response($user, $this->successText, 'Logged in successfully.');
        }else{ 
             
            $this->responseStatusCode = $this->statusCodes->unauthorised;
            $response = api_create_response(2, $this->failureText, 'Please enter valid credentials.');
        } 

        return response()->json($response, $this->responseStatusCode);
    }

/** 
     * Register api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function register(Request $request) 
    { 
        $validator = Validator::make($request->all(), [ 
            'full_name' => 'required', 
            'email' => 'required|email', 
            'password' => 'required', 
            'confirm_password' => 'required|same:password', 
        ]);

        if ($validator->fails()) { 
        
            $response = api_create_response($validator->errors(), $this->failureText, 'Please enter valid input.');
            return response()->json($response, $this->statusCodes->bad_request);            
        }

        $input = $request->all(); 

        $email_response = User::where(['email' => $input['email']])->first();
       
        if(!empty($email_response->id)){
               $res1['email'] = 'This email id allready exit.';
             //return response()->json(['status' => $this->failureText, 'message' => 'This email id all ready exit.']);
             $this->responseStatusCode = $this->statusCodes->bad_request;
             $response = api_create_response($res1, $this->failureText, 'Please enter valid input.');

        }else{
             
            $input['user_type'] = 'user'; 
            $input['password']  = bcrypt($input['password']); 
            $user = User::create($input); 

            $res['user_id']    = $user->id;
            $res['user_type']  = $user->user_type;
            $res['full_name']  = $user->full_name;
            $res['email']      = $user->email;
            $res['created_at'] = $user->created_at;
            $res['token']      =  $user->createToken('MyApp')-> accessToken; 
            
            $this->responseStatusCode = $this->statusCodes->success;
            $response = api_create_response($res, $this->successText, 'Registration successfull.');
            
        }
        return response()->json($response, $this->responseStatusCode);
        
    }


/** 
     * details api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function details(Request $request) 
    { 
      $data = Auth::user();
      
       if($data->user_type == 'admin'){

            $data = User::where(['user_type' => 'user'])->get();
       
            if($data) {
                $this->responseStatusCode = $this->statusCodes->success;
                $response = api_create_response($data, $this->successText, 'List all user records.');

            }else {
                $this->responseStatusCode = $this->statusCodes->bad_request;
                $response = api_create_response(2, $this->failureText, 'No User Found.');
            }
        }else {
                $this->responseStatusCode = $this->statusCodes->bad_request;
                $response = api_create_response(2, $this->failureText, 'Please enter valid credentials.');
            }
       return response()->json($response, $this->responseStatusCode);   
    }
    
       /**
     * Register api 
     * 
     * @return \Illuminate\Http\Response 
     */
    public function logout(Request $request) {
        
        //$userData = Auth::user();
        //pr(Auth::user()->token());

        Auth::user()->token()->revoke();
        Auth::user()->token()->delete();


        if(true){

            $this->responseStatusCode = $this->statusCodes->success;
            $response = api_create_response(2, $this->successText, 'You have been logged out successfully.');
            
        } else {
            
            $this->responseStatusCode = $this->statusCodes->bad_request;
            $response = api_create_response(2, $this->failureText, 'Something went wrong.');
        }
        
        return response()->json($response, $this->responseStatusCode);
    }

    /**
     * Forgot Password Api 
     * 
     * @return \Illuminate\Http\Response 
     */
    public function forgot_password(Request $request) {
        $validator = Validator::make($request->all(), [
                    'email' => 'required|email',
        ]);
        
        if ($validator->fails()) {
            $response = api_create_response($validator->errors(), $this->failureText, 'Please enter valid input.');
            return response()->json($response, $this->statusCodes->bad_request);
        }
        
        // Check if exists
        $validateUserArr = array(
            //'user_type' => $input['user_type'],
            'email' => $request->email,
        );
        $user = User::where($validateUserArr)->first();
        
        if (!empty($user)) {

            // Update Token && Send To Mail
            $token = $this->generateAccountId(50);
            DB::table('password_resets')->insert(['email' => $user->email, 'token' => $token, 'created_at' => date('Y-m-d H:i:s')]);
            $user->token = $token;

            // TODO :: Send mail
            Mail::to($request->email)->send(new UserForgotPassword($user));

            $this->responseStatusCode = $this->statusCodes->success;
            $response = api_create_response(2, $this->successText, 'We have sent an email to your mail.');

        } else {

            $this->responseStatusCode = $this->statusCodes->not_found;
            $response = api_create_response(2, $this->failureText, 'Email not found.');

        }
        
        return response()->json($response, $this->responseStatusCode);
    }


    /**
     * Generate Account ID
     */
    private function generateAccountId($length = 10) {
        //$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    
}