<?php

namespace FII\Parsers;


use Sunra\PhpSimple\HtmlDomParser;

Class FacilityDashboard
{

    public static function parse($__dom)
    {
        $dom = HtmlDomParser::str_get_html($__dom);
        $courses = [];

        $dashboard = $dom->find('#dashboard .tableG > tr');

        foreach($dashboard as $row) {
            $id = $row->getAttribute('id');
            $id = str_replace('trcourse', '', $id);
            $instructor = wpbootsrap_replace_multiple_spaces($row->children[4]->text());
            $instructor = str_replace(['Not Confirmed', 'Confirmed', 'Not'], '', $instructor);
            $instructor = trim($instructor);
            $array = ['id' => $id, 'instructor' => $instructor];
            $courses[] = $array;
        }
        unset($courses[0]);
        array_values($courses);
        return $courses;
    }

}