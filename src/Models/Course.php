<?php

namespace FII\Models;

use FII\Collections\CourseCollection;

Class Course extends \Illuminate\Database\Eloquent\Model {

   protected $table = 'courses';


    public function newCollection(array $models = [])
    {
        $models = apply_filters('fii_new_courses_collection', $models);
        return new CourseCollection($models);
    }

    public static function getCourseById($id)
    {
        // TODO: implement cache
        return static::where('fii_course_id', '=', $id)->first();
    }

    public static function cacheKey()
    {
        $args = func_get_args();
        return md5(__CLASS__.__FUNCTION__.implode($args));
    }


    public static function getCourses()
    {
        $cache = container('cache');

        $key = static::cacheKey();
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

    public function getUrlAttribute()
    {
        return  apply_filters('fii_course_url', 'https://extranet.freedivinginstructors.com/app/public/signup.php?idcourse='.$this->fii_course_id.'&isregistered=n');
    }
}