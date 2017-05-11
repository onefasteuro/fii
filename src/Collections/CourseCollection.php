<?php

namespace FII\Collections;

use Illuminate\Support\Collection;

Class CourseCollection extends Collection
{

    public static function cacheKey()
    {
        return md5(__CLASS__.__FUNCTION__);
    }

}