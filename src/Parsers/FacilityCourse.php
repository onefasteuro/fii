<?php

namespace FII\Parsers;

use Carbon\Carbon;
use FII\Models\Course;
use Sunra\PhpSimple\HtmlDomParser;

Class FacilityCourse
{

    public static function parse($__dom)
    {
        $course_dom = HtmlDomParser::str_get_html($__dom);

        $price_node = $course_dom->find('input[name="tuitionfee"]');
        $price = $price_node[0]->getAttribute('value');

        $max_node = $course_dom->find('input[name="maxcapacity"]');
        $max_capacity = $max_node[0]->getAttribute('value');

        $vacancy_node = $course_dom->find('input[name="vacancy"]');
        $vacancy = $vacancy_node[0]->getAttribute('value');

        $description_node = $course_dom->find('textarea[name="optionaldescription"]');
        $desc = $description_node[0]->text();


        $opt_title = $course_dom->find('input[name="optionaltitle"]');
        $optional_title = $opt_title[0]->getAttribute('value');

        $start_node = $course_dom->find('input[name="ini"]');
        $start_date = $start_node[0]->getAttribute('value');

        $end_node = $course_dom->find('input[name="end"]');
        $end_date = $end_node[0]->getAttribute('value');

        $level_node = $course_dom->find('select[name="idclasslevel"] > option');
        foreach($level_node as $l) {
            if($l->getAttribute('selected') == true) {
                $level_value = $l->getAttribute('value');
                $level_label = $l->text();
                break;
            }
        }

        $model = new Course;
        $model->course_level_id = $level_value;
        $model->start_date = new Carbon($start_date);
        $model->end_date = new Carbon($end_date);
        $model->title = ($optional_title !== '') ? $optional_title : $level_label;

        $model->description = (isset($desc)) ? $desc : '';
        $model->tuition_fee = $price;
        $model->boat_fee = 0;
        $model->max_capacity = $max_capacity;
        $model->vacancy = $vacancy;

        return $model;
    }

}