<?php

/*
 * Developer: Ramayan prasad
 * Date: 23-Apr-2018
 */

// Last Query
//DB->enableQueryLog();
//$queries = DB::getQueryLog();

function pr($obj) {
    echo '<pre>';
    print_r($obj);
    echo '</pre>';
}

function encode($arg) {
    return base64_encode($arg);
}

function decode($arg) {
    return base64_decode($arg);
}

function getFileExt($fileName = '') {

    if (empty($fileName))
        return false;

    $exp = explode('.', $fileName);

    if (count($exp) > 0) {
        return end($exp);
    } else {
        return false;
    }
}

function delFile($file) {
    if (file_exists($file)) {
        @unlink($file);
    }
}

function appDateFormat($date = null) {

    if (empty($date))
        $date = date('Y-m-d');

    if (is_numeric($date)) {
        return date(config('constants.APP_DATE_FORMAT'), $date);
    } else {
        return date(config('constants.APP_DATE_FORMAT'), strtotime($date));
    }
}

function generatePatternNumber($id, $numberCount = 8) {
    return str_pad($id, $numberCount, "0", STR_PAD_LEFT);
}

function generateRandomStr($strLength = 8, $prefix = '') {

    $code = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $code .= "abcdefghijklmnopqrstuvwxyz";
    $code .= "0123456789";
    //$code .= '!@#$%^&*()';

    $token = $prefix;
    $max = strlen($code);

    for ($i = 0; $i < $strLength; $i++)
        $token .= $code[random_int(0, $max - 1)];

    return $token;
}

function getPasswordHashed($password) {
    $passwordHash = env('PASSWORD_HASH', true);

    return ($passwordHash == TRUE) ? md5($password) : $password;
}

function saveNotification($data) {

    $Id = DB::table('notifications')->insertGetId($data);

    return $Id;
}

function sendNotification() {
    
}

function getNotification() {
  if (!empty(session('user_data')['sub_contractors_id']) ) {
    $userId = session('user_data')['sub_contractors_id'];
   //pr($userId);die;
   $userType = 2;
   //pr($userType);die;
   $data = DB::table('notifications')->select('title')->where(['user_id' => $userId,'user_type' => $userType, 'read' => 0])->count();
  } else {
    $userId = session('user_data')['id'];
   //pr($userId);die;
   $userType = session('user_data')['user_type_id'];
   //pr($userType);die;
   $data = DB::table('notifications')->select('title')->where(['user_id' => $userId, 'user_type' => $userType, 'read' => 0])->count();
  }
   //pr($data);die;
   return $data;
}
function listNotification() {
  if (!empty(session('user_data')['sub_contractors_id']) ) {
    $userId = session('user_data')['sub_contractors_id'];
    //pr($userId);die;
     $userType = 2;
    //pr($userType);die;
    $notifications = DB::table('notifications')->select('title','is_read','id')->where(['user_id' => $userId, 'user_type' => $userType])->orderBy('created_at', 'DESC')->limit('25')->get();
    } else {
    $userId = session('user_data')['id'];
    //pr($userId);die;
    $userType = session('user_data')['user_type_id'];
    //pr($userType);die;
    $notifications = DB::table('notifications')->select('title','is_read','id')->where(['user_id' => $userId, 'user_type' => $userType])->orderBy('created_at', 'DESC')->limit('25')->get();
    }
   //pr($data);die;
   return $notifications;
}
function generateCSV($header, $data, $filename = 'result_file.csv') {
    header("Content-type: text/csv");
    header("Content-Disposition: attachment; filename=$filename");
    header("Pragma: no-cache");
    header("Expires: 0");

    $output = fopen("php://output", "w");
    fputcsv($output, $header);
    foreach ($data as $row) {
        fputcsv($output, (array) $row);
    }
    fclose($output);
}

function addLogToTable($post) {
    \DB::table('request_log')->insert(['request' => json_encode($post), 'datetime' => time()]);
}

function unsetData($dataArray = array(), $unsetDataArray = array()) {
    return array_diff_key($dataArray, array_flip($unsetDataArray));
}

/* FILE UPLOADS */

function fileUpload($file, $destinationPath = 'uploads', $fileName = null) {

    $uploadTo = config('filesystems.default');  // Get from config folder configuration

    if (empty($file->getClientOriginalName()))
        return '';

    $ext = $file->getClientOriginalExtension();
    if (empty($fileName))
        $fileName = time() . '-' . mt_rand(1000, 9999) . '.' . $ext;


    if ($uploadTo == 'local') {
        // Local uploads
        //echo $destinationPath . $fileName;die;

        $file->move($destinationPath, $fileName);
        @chmod($destinationPath . $fileName, 0777);

        return $fileName;
    } else if ($uploadTo == 's3') {
        // S3 Uploads

        Storage::disk('s3')->put($destinationPath . $fileName, file_get_contents($file), 'public');
        //$fileName = Storage::disk('s3')->url($fileName);

        return $fileName;
    }



    //pr($file);die;
    //$file = $request->file('image');

    /* //Display File Name
      echo 'File Name: '.$file->getClientOriginalName();
      echo '<br>';

      //Display File Extension
      echo 'File Extension: '.$file->getClientOriginalExtension();
      echo '<br>';

      //Display File Real Path
      echo 'File Real Path: '.$file->getRealPath();
      echo '<br>';

      //Display File Size
      echo 'File Size: '.$file->getSize();
      echo '<br>';

      //Display File Mime Type
      echo 'File Mime Type: '.$file->getMimeType();

      //echo $destinationPath .  $file->getClientOriginalName(); */
}

function generateUploadFilePath($type) {

    $uploadTo = config('filesystems.default');  // Get from config folder configuration

    $fileLocation = config('filelocation.' . $type); //subcontractor-profile-pic


    if ($uploadTo == 'local') {
        // Local uploads
        //echo $destinationPath . $fileName;die;

        return env('PROJECT_PATH') . env('UPLOAD_DIR') . $fileLocation;
    } else if ($uploadTo == 's3') {
        // S3
        //$awsRegion = env('AWS_REGION');
        //$bucketName = env('AWS_BUCKET');
        //return 'https://s3.'. $awsRegion .'.amazonaws.com/' . $bucketName . '/';
        return $fileLocation;
    }
}

function getUploadedFileUrl($type, $fileName) {
    
    $uploadTo = config('filesystems.default');  // Get from config folder configuration
     
    $fileLocation = config('filelocation.' . $type); //subcontractor-profile-pic


    if ($uploadTo == 'local') {
        // Local uploads
        //\echo $destinationPath . $fileName;die;
        return url('/') . '/' . env('UPLOAD_DIR') . $fileLocation . $fileName; 
    } else if ($uploadTo == 's3') {
        // S3

        $awsRegion = env('AWS_REGION');
        $bucketName = env('AWS_BUCKET');

        return 'https://s3.' . $awsRegion . '.amazonaws.com/' . $bucketName . '/' . $fileLocation . $fileName;
    }
}

// SESSION
function checkSesion() {

    //pr(session('user_data'));die;
    if (empty(session('user_data'))) {
        header('location: /');
        exit;
    }
}

function sendSMTPMail($view, $mailData) {
    /* $view = 'mails.set-password';
      $mailData = array(
      'subject' => 'Test',
      'name' => 'Ramayan',
      'email' => 'ramayan@apptology.in',
      'token' => 'test'
      ); */

    \Mail::send($view, $mailData, function ($message) use($mailData) {
        //pr($mailData);die;
        $message->to($mailData['email'])
                ->from(env('MAIL_FROMEMAIL'), env('FROMNAME'))
                ->subject($mailData['subject'] . ' - ' . env('APP_NAME'));
    });
}

##################### ROLE MANAGEMENT ####################

function roles($role = '') {
    $userData = Session::get('user_data');
    //pr($userData);die;

    if (!empty($userData['permissions'])) {
        if (!empty($userData['permissions'][$role])) {
            return $userData['permissions'][$role];
        }
    }

    return array();


    /* $query = DB::table('hta_permission_role as r');
      $query->join('hta_permissions as p', 'r.permission_id', '=', 'p.id');
      $query->join('hta_role_category as c', 'r.role_cat_id', '=', 'c.id');
      $query->select('c.cate_name', 'c.display_name');
      if (!empty($parm)) {
      $query->where('c.cate_name', $parm);
      }
      $query->where('r.user_type_id', $userData['users_type']);
      $permissions = $query->first(); */

//    return $permissions;
}

function permissions($role = '', $permission = '') {

    $userData = Session::get('user_data');
//    pr($userData);die;

    if (!empty($userData['permissions'])) {
        if (!empty($userData['permissions'][$role])) {

            //pr($userData['permissions'][$role]);die;

            if (in_array($permission, $userData['permissions'][$role]))
                return $userData['permissions'][$role];
        }
    }

    return array();


    /* $query = DB::table('hta_permission_role as r');
      $query->join('hta_permissions as p', 'r.permission_id', '=', 'p.id');
      $query->join('hta_role_category as c', 'r.role_cat_id', '=', 'c.id');
      $query->select('p.*');
      if (!empty($parm)) {
      $query->where('p.name', $parm[1]);
      $query->where('c.cate_name', $parm[0]);
      }
      $query->where('r.user_type_id', $userData['id']);
      $permissions = $query->get();

      return $permissions; */
}

function role_categories() {
    $query = DB::table('hta_role_category')->select('id', 'cate_name', 'display_name')->get();
    $role_categories = array();
    foreach ($query as $key => $value) {
        $value->permissions = get_permistions();

        array_push($role_categories, $value);
    }

    return $role_categories;
}

function get_permistions() {
    $permistions = DB::table('hta_permissions')->select('id', 'name')->get();

    return $permistions;
}

function assigned_permissions($cId, $pId, $uId) {
    $perm = DB::table('hta_permission_role')
            ->select('id')
            ->where('role_cat_id', $cId)
            ->where('permission_id', $pId)
            ->where('user_type_id', $uId)
            ->count();
    $assiged = $perm > 0 ? 'checked' : '';
    return $assiged;
}

##################### END OF ROLE MANAGEMENT ####################

function randomOtp() {
    $alphabet = '1234567890';
    $pass = array();
    $alphaLength = strlen($alphabet) - 1;
    for ($i = 0; $i < 4; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass);
}




function generateQrCodeURL($data = array()) {

    $dataString = urlencode(implode('____', $data));

    //$googleApiUrl = 'http://chart.apis.google.com/chart?chs=450x450&cht=qr&chld=L|1&chl='.$dataString;
    $googleApiUrl = 'http://chart.apis.google.com/chart?chs=255x255&cht=qr&chld=L|1&chl=' . $dataString;


    return $googleApiUrl;
}

function getLatLongFromAddress($address){
    
    //$address = 'A-12/3, Phase 1, Naraina Industrial Area, New Delhi - 110028';
    $address = str_replace(' ', '+', $address);
    //echo $address;
    
    //$googleApiKey = 'AIzaSyC47EnCU_VAtFY6PqC6oB2lTEYnGbhA9pA';      // Ramayan google account
    //$googleApiKey = 'AIzaSyA1W0w3fSvwpk5fd_ct3tUihfc3qXZ3tHM';      // Ben google account
    
    $googleApiKey = env('GOOGLE_API_KEY');
    
    $googleLatLongURL = 'https://maps.googleapis.com/maps/api/geocode/json?key='. $googleApiKey .'&address='.$address;
    
    $googleData = callCURL('GET', $googleLatLongURL, false);
    //pr($googleData);die;
    
    $googleData = json_decode($googleData);
    //pr($googleData);die;
    
    if(empty($googleData->results[0])) {
        sleep(0.2);
        getLatLongFromAddress($address);
    }
    
    
    $latitude = !empty($googleData->results[0]->geometry->location->lat) ? $googleData->results[0]->geometry->location->lat : '';
    $longitude = !empty($googleData->results[0]->geometry->location->lng) ? $googleData->results[0]->geometry->location->lng : '';
    
    return [
        'lat' => $latitude,
        'long' => $longitude,
    ];
}


function callCURL($method, $url, $data, $headers=array()) {
    
    // HELP URL: https://www.weichieprojects.com/blog/curl-api-calls-with-php/
    
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);

    switch ($method) {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);
            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            break;
        default:
            if ($data)
                $url = sprintf("%s?%s", $url, http_build_query($data));
    }
    //echo $url;//die;

    // OPTIONS:
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    
    if(!empty($headers)) {
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $headers = array(
            'APIKEY: 111111111111111111111',
            'Content-Type: application/json',
        );
    }

    // EXECUTE:
    $result = curl_exec($curl);
    if (!$result) {
        die("Connection Failure");
    }
    curl_close($curl);
    
    return $result;
}

function Get_Api_secret_key()
{
    $data = [
             'key'       => str_shuffle(md5(time())),
             'secret_key'=> md5(time()),
             'status'    => '1',
            ];
    return $data;
}