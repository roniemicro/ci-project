<?php

namespace Emicro\Base;

class Loader extends \CI_Loader {

    protected $CI;

    public function __construct()
    {
        parent::__construct();
        $this->CI =& get_instance();
    }

    public function viewWithLayout($view, $data = null, $return = false)
    {
        $loadedData = array();
        $loadedData['content'] = $this->view($view,$data,true);

        return $this->view("layouts/".$this->CI->get_layout(), $loadedData, $return);
    }
}
