<?php
namespace Emicro\Base;

use Emicro\Twig\Dummy;
use Emicro\Twig\Loader;

class Controller extends \CI_Controller {

    private $_isAjaxRequest = false;
    private $_layout = 'main';
    private $_twig = null;

    function __construct(){
        parent::__construct();
        $this->_isAjaxRequest=($this->input->get('ajax')|| $this->input->is_ajax_request());
    }

    protected function show_error($msg=null,$code=null,$header=null){
        if($this->_isAjaxRequest){
            $msg=json_encode(array('success'=>false,'msg'=>$msg));
            set_status_header($code);
            die($msg);
        }
        if($code==404){
          show_404();
        }
        show_error($msg,$code,$header);
    }

    public function get_layout()
    {
        return $this->_layout;
    }

    public function set_layout($layout)
    {
        return $this->_layout = $layout;
    }

    public function isAjax()
    {
        return $this->_isAjaxRequest;
    }

    public function render($template, $data=array(), $return = false)
    {
        if($this->isTwigTemplate($template)){
            $output = $this->twig()->render($template, $data);
            if($return){
                return $output;
            }
            echo $output;
        }else{
            if($this->isAjax()){
                return $this->load->view($template, $data, $return);
            }else{
                return $this->load->viewWithLayout($template, $data, $return);
            }
        }
    }

    private function isTwigTemplate($template)
    {
        return substr(strrchr($template,'.'),1)=='twig';
    }


    public function _render($template, $data=array(), $return = false)
    {
        return $this->load->view($template, $data, $return);
    }

    public function twig()
    {
        if(!$this->_twig){
            $this->_twigInit();
        }

        return $this->_twig;
    }

    private function _twigInit()
    {
        if($this->config->item('enable_twig')){
            $twigDirectory = $this->config->item('twig_dir');
            if(!$twigDirectory){
                $twigDirectory = 'twig';
            }

            $templatesPath = APPPATH.'views/'.$twigDirectory;
            $loader = new \Twig_Loader_Filesystem($templatesPath);
            $this->_twig = new Loader($loader);
        }else{
            $this->_twig = new Dummy();
        }
    }
}

/* End of file Controller.php */
/* Location: ./Emicro/Base/Controller.php */