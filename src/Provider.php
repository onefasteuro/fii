<?php

namespace FII;

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

    public function persist($courses)
    {
        //clean the records
        Course::truncate();
        $courses->each(function($course){
            $course->save();
        });
        return $this;
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


    public function cache($data, $key)
    {
        $this->cache->put($key, $data, CACHE_WEEK);
    }
}