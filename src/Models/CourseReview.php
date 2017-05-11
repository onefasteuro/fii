<?php

namespace FII\Models;

Class CourseReview extends \Illuminate\Database\Eloquent\Model {

    protected $table = 'course_reviews';


    public static function cacheKey()
    {
        return md5(__CLASS__.__FUNCTION__);
    }

    public static function getLatestEntry($date)
    {
        return static::where('date', '>', $date)->first();
    }

    public static function getAverageRating()
    {
        $rating = static::avg('rating');
        return number_format($rating, 2);
    }

    public static function getReviews()
    {
        $data = remember(static::cacheKey(), CACHE_WEEK, function(){
            return static::all();
        });
        return $data;
    }



}