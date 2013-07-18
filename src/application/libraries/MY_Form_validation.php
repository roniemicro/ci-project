<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Form Validation Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Validation
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/form_validation.html
 */
class MY_Form_validation extends CI_Form_validation {

	/**
	 * Constructor
	 */
	public function __construct($rules = array())
	{
        parent::__construct($rules);
	}

	// --------------------------------------------------------------------

	/**
	 * Error array
	 *
	 * Returns the error messages as a array, wrapped in the error delimiters
	 *
	 * @access	public
	 * @return	array
	 */
	public function get_errors()
	{
        if(!($this->CI->input->get('ajax')|| $this->CI->input->is_ajax_request())){
            return validation_errors();
        }
        $ret_arr=array();
        foreach ($this->_error_array as $val)
        {
            if ($val != '')
            {
                $ret_arr[]= $val;
            }
        }
        return $ret_arr;
	}

	// --------------------------------------------------------------------
}
// END Form Validation Class

/* End of file Form_validation.php */
/* Location: ./application/core/MY_Form_validation.php */
