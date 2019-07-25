<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ads extends Model
{
      protected $fillable = [
       'ads_position', 'image', 'image_url', 'links', 'start_date', 'end_date', 'status', 'created_at',
        'updated_at'
    ];

	protected $table    = 'ads';
	public $timestamps  = true;
    
    static public function insertAds($postedData)
	  {
	        $response = New ads($postedData);
	        
	        if($response->save()){

	            $lastInsertedID = $response->id;
	            return $lastInsertedID;
	        }
	  }	
}
