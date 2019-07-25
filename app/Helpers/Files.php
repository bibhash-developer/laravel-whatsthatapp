<?php

/*
 * Developer: Ramayan prasad
 * Date: 29-Oct-2018
 * Description: File helper functions
 */


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
        //echo $destinationPath . $fileName;die;

        return url() . '/' . env('UPLOAD_DIR') . $fileLocation;
    } else if ($uploadTo == 's3') {
        // S3

        $awsRegion = env('AWS_REGION');
        $bucketName = env('AWS_BUCKET');

        return 'https://s3.' . $awsRegion . '.amazonaws.com/' . $bucketName . '/' . $fileLocation . $fileName;
    }
}