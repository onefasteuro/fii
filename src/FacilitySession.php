<?php

namespace FII;

use Sunra\PhpSimple\HtmlDomParser;
use Carbon\Carbon;
use Requests_Session;
use Illuminate\Support\Collection;

class FacilitySession
{

    protected $session;

    //which side are we crawling?
    protected $crawl_url_type = 'facility';
    protected $crawl_url = [
        'facility' => '/app/F/courselist.php?createdby=2',
        'instructor' => '/app/F/courselist.php?createdby=1'
    ];

    public function __construct($session)
    {
        $this->session = $session;
    }


    protected function isInstructorCourse()
    {
        return ($this->crawl_url_type) == 'instructor' ? true : false;
    }

    protected static function createNewCourse($course)
    {
        $model = new Course;

        $model->fii_course_id = $course['id'];
        $model->instructor = $course['instructor'];
        $model->url = 'https://extranet.freedivinginstructors.com/app/public/signup.php?idcourse='.$course['id'].'&isregistered=n';

        //fetch the location
        $model->location = "2424 N. Federal Highway,\nPompano Beach, FL";
        return $model;
    }


    public function setCrawlUrl($type) {
        $this->crawl_url_type = $type;
        return $this;
    }

    public function crawl()
    {
        $session = $this->session;

        $courses_page = $session->get($this->crawl_url[$this->crawl_url_type]);
        $dom = HtmlDomParser::str_get_html($courses_page->body);
        $dashboard = $dom->find('#dashboard .tableG > tr');
        unset($dashboard[0]);


        $courses = new Collection;

        //get the courses id
        foreach($dashboard as $row) {
            $id = $row->getAttribute('id');
            $id = str_replace('trcourse', '', $id);
            $instructor = wpbootsrap_replace_multiple_spaces($row->children[4]->text());
            $instructor = str_replace(['Not Confirmed', 'Confirmed', 'Not'], '', $instructor);
            $instructor = trim($instructor);
            $array = ['id' => $id, 'instructor' => $instructor];
            $courses->push($array);
        }

        if($this->isInstructorCourse()) {
            return $this->parseInstructorDashboard($dashboard, $courses);
        }
        else {
            return $this->parseFacilityDashboard($dashboard, $courses);
        }
    }

    private function parseInstructorDashboard($dashboard, Collection $courses)
    {
        $session = $this->session;

        $courses_list = new Collection;


        foreach($dashboard as $k => $v) {
            foreach($v->children as $c => $child){
                switch($c) {
                    case 1:
                        $id = wpbootsrap_replace_multiple_spaces($child->text());
                        break;
                    case 2:
                        $level = wpbootsrap_replace_multiple_spaces($child->text());
                        break;
                    case 4:
                        $instructor = wpbootsrap_replace_multiple_spaces($child->text());
                        break;
                    case 6:
                        $start_date = wpbootsrap_replace_multiple_spaces($child->text());
                        break;
                    case 7:
                        $max_capacity = wpbootsrap_replace_multiple_spaces($child->text());
                        break;
                    case 8:
                        $vacancy = wpbootsrap_replace_multiple_spaces($child->text());
                        break;
                }
            }
        }

    }

    private function parseFacilityDashboard($dashboard, Collection $courses)
    {
        $session = $this->session;

        $location_page = $session->get('/app/F/instructoredit.php');
        $location_dom = HtmlDomParser::str_get_html($location_page->body);

        $courses_list = new Collection;

        $courses->each(function($course) use($session, $courses_list, $location_dom){
            $course_page = $session->get('/app/F/course.php?idcourse='.$course['id']);

            $course_dom = HtmlDomParser::str_get_html($course_page->body);

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

            $model = static::createNewCourse($course);

            $model->course_level_id = $level_value;
            $model->start_date = new Carbon($start_date);
            $model->end_date = new Carbon($end_date);
            $model->title = ($optional_title !== '') ? $optional_title : $level_label;

            $model->description = (isset($desc)) ? $desc : '';
            $model->tuition_fee = $price;
            $model->boat_fee = 0;
            $model->max_capacity = $max_capacity;
            $model->vacancy = $vacancy;

            $courses_list->push($model);
        });

        return $courses_list;
    }
}