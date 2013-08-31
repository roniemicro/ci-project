<?php


class Welcome extends MY_Controller {

	public function access_map(){
        return array(
            'index'=>'view'
        );
    }

    public function index()
	{
        //$this->render('welcome.html.twig');
        $this->render('welcome_message', array('title'=>'Welcome to Codeigniter!'));
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */