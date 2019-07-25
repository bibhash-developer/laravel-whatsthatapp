<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
	use SoftDeletes;
    protected $fillable = [
       'category_name', 'category_image', 'created_by', 'created_at'
    ];

	protected $table    = 'category';
	public $timestamps  = true;


    //protected $hidden = ['updated_at', 'deleted_at'];



	static public function insertCategory($postedData)
	  {
	        $response = New category($postedData);
	        
	        if($response->save()){

	            $lastInsertedID = $response->id;
	            return $lastInsertedID;
	        }
	  }	
}
