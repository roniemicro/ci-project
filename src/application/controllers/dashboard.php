<?php

use Emicro\Base\Controller;

class Dashboard extends Controller {

	public function access_map(){
        return array(
            'index'=>'view'
        );
    }

    public function index()
	{
        //$this->render('welcome.html.twig');
        $this->render('welcome_message');
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */