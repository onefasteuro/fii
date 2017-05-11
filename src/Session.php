<?php

namespace FII;
use Requests_Session;


class Session
{
    protected $session;

    public function __construct(Requests_Session $session)
    {
        $this->session = $session;
    }


    public function crawl($endpoint)
    {
        $dom = $this->session->get($endpoint);
        return $dom;
    }
}