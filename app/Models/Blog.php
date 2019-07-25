<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;



class Blog extends Model
{
   
     protected $fillable=['author_name', 'category_name', 'image','title','description','status'];

     protected $table    = 'blogs';
	 public $timestamps  = true;
}
