<?php

namespace FII\Collections;


use Illuminate\Support\Collection;

Class ReviewCollection extends Collection
{
    protected $cache_key = 'FII_ReviewCollection';

    public static function cacheKey()
    {
        return md5(__CLASS__.__FUNCTION__);
    }
}