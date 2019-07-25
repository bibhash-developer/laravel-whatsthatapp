<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $fillable = [
       'category_id', 'user_id', 'app_title', 'app_icon', 'affiliate_link', 'description', 'video_link',
        'app_image', 'download_link', 'updated_by', 'created_at', 'updated_at'
    ];

	protected $table    = 'application';
	public $timestamps  = true;
    
    static public function insertApplicate($postedData)
	  {
	        $response = New application($postedData);
	        
	        if($response->save()){

	            $lastInsertedID = $response->id;
	            return $lastInsertedID;
	        }
	  }	
}
