<?php

namespace FII;

use Sunra\PhpSimple\HtmlDomParser;
use Carbon\Carbon;
use Requests_Session;
use Illuminate\Support\Collection;

class PublicSession
{

    protected $session;


    public function __construct($session)
    {
        $this->session = $session;
    }

    public function testimonials()
    {
        $html = $this->crawl('/testimonials');
        $dom = HtmlDomParser::str_get_html($html->body);

        $comments_list = $dom->find('ul#comment-list');
    }


    public function crawl($endpoint)
    {
        $session = $this->session;
        return $this->session->get($endpoint);
    }
}