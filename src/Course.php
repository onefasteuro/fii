<?php

namespace FII;

Class Course extends \Illuminate\Database\Eloquent\Model {

   protected $table = 'courses';

    const COURSES_CACHE_KEY = 'fii_all_courses';

    public static function getCacheKey()
    {
        $key = md5(self::COURSES_CACHE_KEY);
        return $key;
    }

    public static function getFiiCourse($id)
    {
        return static::where('fii_course_id','=', $id)->first();
    }


    public static function getCourses()
    {
        $key = static::getCacheKey();
        $response = remember($key, CACHE_WEEK, function(){
            return static::orderBy('start_date', 'DESC')->get();
        });
        return $response;

    }
}