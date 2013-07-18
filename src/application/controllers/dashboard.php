<?php

use Emicro\Base\Controller;

class Dashboard extends Controller {

	public function index()
	{
        $this->render('welcome.html.twig');
        //$this->load->view('welcome_message');
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */