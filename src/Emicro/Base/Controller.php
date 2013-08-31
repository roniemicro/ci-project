<?php
namespace Emicro\Base;

use Emicro\Twig\Dummy;
use Emicro\Twig\Loader;

class Controller extends \CI_Controller {

    private $_isAjaxRequest = false;
    private $_layout = 'main';
    private $_twig = null;
    private $_twigPath = null;

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
            $twig = $this->twig();
            $data['__FILE__'] = $this->getTwigPath($template);
            $data['APPPATH'] = realpath(APPPATH );
            $data['DIRECTORY_SEPARATOR'] = DIRECTORY_SEPARATOR;
            $output = $twig->render($template, $data);
            if($return){
                return $output;
            }
            $this->output->append_output($output);
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

    public function getTwigPath($template)
    {
        return $this->_twigPath . DIRECTORY_SEPARATOR . $template;
    }

    private function _twigInit()
    {
        if($this->config->item('enable_twig')){
            $twigDirectory = $this->config->item('twig_dir');
            if(!$twigDirectory){
                $twigDirectory = 'twig';
            }

            $templatesPath = APPPATH.'views/'.$twigDirectory;

            $this->_twigPath = realpath($templatesPath);

            if(class_exists('\Twig_Loader_Filesystem')){
                $loader = new \Twig_Loader_Filesystem($templatesPath);
                $this->_twig = new Loader($loader);
            }else{
                show_error('Twig is not installed. Install Twig first by run the command "composer update twig/twig"');
            }
        }else{
            $this->_twig = new Dummy();
        }
    }
}

/* End of file Controller.php */
/* Location: ./Emicro/Base/Controller.php */