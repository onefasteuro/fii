<?php

namespace FII\Models;

Class Course extends \Illuminate\Database\Eloquent\Model {

   protected $table = 'courses';


    public static function getCourseById($id)
    {
        // TODO: implement cache
        return static::where('fii_course_id', '=', $id)->first();
    }

    public static function cacheKey()
    {
        return wpbootstrap_create_cache_key('FII_CourseCollection');
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

        $key = wpbootstrap_create_cache_key(__CLASS__, __FUNCTION__, $level);
        $response = $cache->remember($key, CACHE_WEEK, function() use(&$level) {
            return static::orderBy('start_date', 'DESC')->where('course_level_id', '=', $level)->get();
        });
        return $response;
    }
}