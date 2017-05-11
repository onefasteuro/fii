<?php

namespace FII;

use FII\Models\Course;
use FII\Models\CourseReview;
use Requests_Session;

class Provider
{
    protected $session;
    protected $config;
    protected $cache;

    public function __construct($config, $cache)
    {
        $this->config = $config;
        $this->cache = $cache;
    }


    public function persist($data)
    {
        $class = get_class($data);
        if(preg_match('/course/i', $class)){
            Course::truncate();
            $data->each(function($course){
                $course->save();
            });
            return $this;
        }

        if(preg_match('/review/i', $class)) {
            CourseReview::truncate();
            $data->each(function($review){
                $review->save();
            });
            return $this;
        }
    }



    /*
     *
     * Base site crawling session
     *
     */
    public function site()
    {
        $session = new Requests_Session('http://freedivinginstructors.com/fii');
        return new Session($session);

    }

    /*
     *
     * Facility Session
     *
     */
    public function facility()
    {
        $session = new Requests_Session($this->config['base_url']);
        $session->user_agent = $this->config['user_agent'];
        $session->post('/app/login-check.php', [], $this->config['F']);
        return new Session($session);
    }

    public function instructor()
    {
        $session = new Requests_Session($this->config['base_url']);
        $session->user_agent = $this->config['user_agent'];
        $session->post('/app/login-check.php', [], $this->config['I']);
        return new Session($session);
    }


    public function cache($data)
    {
        $key = forward_static_call_array([$data, 'cacheKey'], []);
        $this->cache->put($key, $data, CACHE_WEEK);
        return $this;
    }
}