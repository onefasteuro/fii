<?php

namespace FII;

use Sunra\PhpSimple\HtmlDomParser;
use Carbon\Carbon;
use Requests_Session;
use Illuminate\Support\Collection;

class FacilitySession
{

    protected $session;
    protected $courses;

    public function __construct($session)
    {
        $this->session = $session;
    }


    public function setCourses(Collection $courses)
    {
        $this->courses = $courses;
        return $this;
    }




    public function crawl()
    {
        $session = $this->session;

        $courses_page = $session->get('/app/F/courselist.php');
        $dom = HtmlDomParser::str_get_html($courses_page->body);
        $dashboard = $dom->find('#dashboard .tableG > tr');
        unset($dashboard[0]);

        $courses = new Collection;

        foreach($dashboard as $row) {
            $id = $row->getAttribute('id');
            $courses->push(str_replace('trcourse', '', $id));
        }

        $location_page = $session->get('/app/F/instructoredit.php');
        $location_dom = HtmlDomParser::str_get_html($location_page->body);


        $courses_list = new \Illuminate\Support\Collection;

        $courses->each(function($course) use($session, $courses_list, $location_dom){
            $course_page = $session->get('/app/F/course.php?idcourse='.$course);

            //link doesnt work, go away
            if(trim($course_page->body) == 'error') return;

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

            $instructor_node = $course_dom->find('#instructortext');
            $instructor = $instructor_node[0]->text();
            $instructor = preg_replace('/\s+/', '', $instructor);
            preg_match('/\#[0-9]+/', $instructor, $matches);
            if(count($matches) > 0) {
                $instructor_id = str_replace('#', '', $matches[0]);
            }
            else {
                $instructor_id = 0;
            }


            $level_node = $course_dom->find('select[name="idclasslevel"] > option');
            foreach($level_node as $l) {
                if($l->getAttribute('selected') == true) {
                    $level_value = $l->getAttribute('value');
                    $level_label = $l->text();
                    break;
                }
            }

            $model = new Course;
            $model->fii_course_id = $course;
            $model->course_level_id = $level_value;
            $model->title = ($optional_title !== '') ? $optional_title : $level_label;
            $model->instructor = $instructor_id;

            //fetch the location
            $model->location = "2424 N. Federal Highway,\nPompano Beach, FL";

            //ends location fetch

            $model->description = (isset($desc)) ? $desc : '';
            $model->tuition_fee = $price;
            $model->boat_fee = 0;
            $model->max_capacity = $max_capacity;
            $model->vacancy = $vacancy;
            $model->instructor = '';
            $model->start_date = new Carbon($start_date);
            $model->end_date = new Carbon($end_date);
            $model->url = 'https://extranet.freedivinginstructors.com/app/public/signup.php?idcourse='.$course.'&isregistered=n';
            $courses_list->push($model);
        });

        $this->setCourses($courses_list);

        return $courses_list;
    }

    /**
     *
     * get our courses list
     *
     * @return mixed
     */
    public function courses()
    {
        return $this->courses;
    }

    public function persist()
    {
        //clean the records
        Course::truncate();
        $this->courses->each(function($course){
            $course->save();
        });
        return $this;
    }
}