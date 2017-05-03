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


    /**
     *
     * Starts our browsing session on the site
     *
     * @param $as
     * @return Requests_Session
     */
    public function session($as)
    {
        $session = new Requests_Session($this->config['base_url']);
        $session->user_agent = $this->config['user_agent'];
        $session->post('/app/login-check.php', [], $this->config[$as]);
        switch($as) {
            case 'F':
                return new FacilitySession($session);
            break;

            case 'I':
                return new InstructorSession($session);
            break;
        }
    }

    public function getCourse($id)
    {

    }

    public function getCourses()
    {
        $key = Course::getCacheKey();
        $response = $this->cache->remember($key, CACHE_WEEK, function(){
            return Course::orderBy('start_date', 'DESC')->get();
        });
        return $response;
    }


    public function cache($data)
    {
        $key = Course::getCacheKey();
        $this->cache->put($key, $data, CACHE_WEEK);
    }
}