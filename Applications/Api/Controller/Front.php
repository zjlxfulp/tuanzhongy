<?php

namespace Api\Controller;
use Core\Output;

class Front extends Base
{
    public function __construct() {
        parent::__construct();
    }

    public function get_areas() {
        $citys = $this->db->select('*')->from('single_city')->query();
        $downs = $this->db->select('*')->from('single_down')->query();
        $areas = array();
        foreach ($citys as $val) {
            $val['country_id'] = 999999;
            $areas[$val['country_id']][$val['id']] = $val['city'];
        }

        foreach ($downs as $val) {
            $areas[$val['city_id']][$val['id']] = $val['down'];
        }
        unset($citys);
        unset($downs);

        Output::json($areas);
    }

    public function get_brands() {
        $brand = $this->db->select('id,brand_name')->from('single_brand')->query();
        Output::json($brand);
    }

    public function module() {
        $mod = $this->input->get('mod');
        $mod = str_replace('.shtml' , '', $mod);
        $prams = explode('-', $mod);
        $func = isset($prams[0]) ? $prams[0] : 0;
        $type = isset($prams[1]) ? $prams[1] : 0;
        $id = isset($prams[2]) ? $prams[2] : 0;

        //显示404
        if(!$type || !$func){
            if(is_file(  APIVIEWS . '404.html')) {
                include(APIVIEWS . '404.html');
            }
        } else {
            $this->createHtml($func, $type, $id);
        }
    }

    /*
     * $dirname 模版文件目录
     * $type 模版类型
     * $id 分页数或者id
     * */
    private function createHtml($dirname, $type, $id = 0) {
        if(!is_dir(APIVIEWS . $dirname )) {
            mkdir(APIVIEWS . $dirname,0777,true);
        }

        $outdirname = $dirname;
        $tpl = TPLPATH . $dirname . '/'. $dirname . '-' . $type . '.php';
        if(!is_file($tpl)) {
            $dirname = 'articles';
        }

        $tpl = TPLPATH . $dirname . '/'. $dirname . '-' . $type . '.php';
        $out = APIVIEWS . $outdirname . '/'. $outdirname . '-' . $type .'-' . $id . '.shtml';

        $flag = false;

        if(is_file( $tpl )) {
            include( $tpl );
        }

        if($flag == true) {
            file_put_contents($out, ob_get_contents());
        } else {
            ob_get_clean();
            ob_start();
            if(is_file(  APIVIEWS . '404.html')) {
                include(APIVIEWS . '404.html');
            }
        }
    }

    public function coordinates() {
        $city = $this->input->get_post('c');
        $down = $this->input->get_post('d');
        $brand = $this->input->get_post('b');
        $where = ' 1=1 ';

        if($city) {
            $id = $city;
            $table = 'single_city';
            $where .= " and city_id={$city} ";
        } else {
            $id = 1;
            $table = 'single_country';
        }

        if($down) {
            $where .= " and down_id={$down} ";
        }

        if($brand) {
            $where .= " and brand_id={$brand} ";
        }

        $coordinates = $this->db->select('store_name,coordinate')->from('single_store')->where($where . 'and coordinate!=""')->query();

        $center = $coors = array();
        $cen = $this->db
            ->select('coordinate')
            ->from($table)
            ->where("id={$id}")
            ->single();
        $cen = str_replace(' ', '', $cen);
        list($center['lat'], $center['lng']) = explode(',', $cen);

        if(!empty($coordinates)) {
            foreach ($coordinates as $key => $coordinate) {
                $coors['coordinates'][$key]['title'] = $coordinate['store_name'];
                $coordinate = str_replace(' ', '', $coordinate['coordinate']);
                list($coors['coordinates'][$key]['lat'], $coors['coordinates'][$key]['lng']) = explode(',', $coordinate);
            }
        } else {
            $coors['coordinates'] = array();
        }
        $coors['center'] = $center;
        Output::json($coors);
    }
}