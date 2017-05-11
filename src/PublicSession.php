<?php

namespace FII;

use Sunra\PhpSimple\HtmlDomParser;
use Carbon\Carbon;
use Requests_Session;

class PublicSession
{

    protected $session;

    public function __construct($session)
    {
        $this->session = $session;
    }

    protected function parseTestimonials($dom, ReviewCollection $reviews)
    {
        $comments_list = $dom->find('#comment-list');

        foreach($comments_list as $k => $node)
        {
            $child = $node->find('li')[0];

            $text = $child->outertext();
            preg_match('/\"score\"\:\s[0-9]{1}/', $text, $matches);
            $rating = str_replace(['"', 'score', ':', ' '], '', $matches[0]);
            $text = wpbootsrap_replace_multiple_spaces($text);

            $feedback = null;
            $instructor = null;

            preg_match('/\<strong\>Instructor\:\<\/strong\>[a-zA-Z\s]+/', $text, $matches);
            if(count($matches) > 0) {
                $instructor = str_replace('<strong>Instructor:</strong>', '', $matches[0]);
            }

            $feedback = preg_match('/\<strong\>Feedback\:\<\/strong\>.*/', $text, $matches);
            if(count($matches) > 0) {
                $feedback = str_replace(['<strong>Feedback:</strong>', '</p>', '</li>'], '', $matches[0]);
                $feedback = trim($feedback);
            }

            $name = $child->find('p.name')[0]->text();
            $date = $child->find('p.date')[0]->text();

            $name = str_replace('verified student', '', $name);
            $name = wpbootsrap_replace_multiple_spaces($name);

            $review = new CourseReview;
            $review->name = $name;
            $review->rating = $rating;
            $review->for_instructor = $instructor;
            $review->feedback = $feedback;
            $review->date = $date;
            $reviews->push($review);
        }
    }

    public function testimonials()
    {
        $count = 1;
        $reviews = new ReviewCollection;
        do {
            $html = $this->crawl('/fii/testimonials/page:'.$count);
            $dom = HtmlDomParser::str_get_html($html->body);
            $this->parseTestimonials($dom, $reviews);
            $count++;
        }
        while($html->status_code == 200);

        return $reviews;
    }


    public function crawl($endpoint)
    {
        $session = $this->session;
        return $this->session->get($endpoint);
    }
}