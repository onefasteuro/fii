<?php

namespace FII\Models;

Class CourseReview extends \Illuminate\Database\Eloquent\Model {

   protected $table = 'courses_reviews';


    public static function cacheKey()
    {
        return wpbootstrap_create_cache_key('FII_ReviewCollection');
    }

}