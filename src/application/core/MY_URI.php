<?php

class MY_URI extends CI_URI {

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Fetch a URI "routed" Absolute Segment(excluding the controller and action)
     *
     * This function returns the re-routed URI segment (assuming routing rules are used)
     * based on the number provided.  If there is no routing this function returns the
     * same result as $this->segment() except, excluding the controller and action
     *
     * @access	public
     * @param	integer
     * @param	bool
     * @return	string
     */
    function asegment($n, $no_result = FALSE)
    {
        return $this->rsegment($n+2);
    }

}

/* End of file MY_URI.php */
/* Location: ./application/core/MY_URI.php */