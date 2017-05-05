<?php

namespace FII;

Class Course extends \Illuminate\Database\Eloquent\Model {

   protected $table = 'courses';


    public static function getAllCoursesCacheKey()
    {
        return wpbootstrap_create_cache_key('FII_Course_GetCourses');
    }


    public static function getCourses()
    {
        $cache = container('cache');

        $key = static::getAllCoursesCacheKey();
        $response = $cache->remember($key, CACHE_WEEK, function(){
            return static::orderBy('start_date', 'DESC')->get();
        });
        return $response;
    }



    public static function getCoursesByLevel($level)
    {
        $cache = container('cache');

        $key = wpbootstrap_create_cache_key(__CLASS__, __FUNC__, $level);
        $response = $cache->remember($key, CACHE_WEEK, function() use(&$level) {
            return static::orderBy('start_date', 'DESC')->where('course_level_id', '=', $level)->get();
        });
        return $response;
    }
}