<?php

namespace FII\Parsers;

use FII\Models\CourseReview;
use Sunra\PhpSimple\HtmlDomParser;

Class Testimonials
{

    public static function parse($__dom)
    {
        $dom = HtmlDomParser::str_get_html($__dom);

        $comments_list = $dom->find('#comment-list');
        $reviews = [];
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

            //create our model and populates variables
            $review = new CourseReview;
            $review->name = $name;
            $review->rating = $rating;
            $review->for_instructor = $instructor;
            $review->feedback = $feedback;
            $review->date = $date;

            //review source
            $review->source = 'fii';

            $reviews[] = $review;
        }

        return $reviews;
    }

}