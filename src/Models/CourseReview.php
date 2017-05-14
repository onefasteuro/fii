<?php

namespace FII\Models;

Class CourseReview extends \Illuminate\Database\Eloquent\Model {

    protected $table = 'course_reviews';


    public function newCollection(array $models = [])
    {
        return new ReviewCollection($models);
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
        $key = wpbootstrap_create_cache_key('fii_courses_reviews');
        $data = remember(static::cacheKey(), CACHE_WEEK, function(){
            return static::all();
        });
        return $data;
    }

    public function getReviewsFromSource($source)
    {
        $key = wpbootstrap_create_cache_key('fii_courses_reviews', $source);
        $data = remember($key, CACHE_WEEK, function() use(&$source) {
            return static::where('source', '=', $source)->get();
        });
        return $data;
    }

}