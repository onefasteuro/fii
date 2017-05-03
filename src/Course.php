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
}