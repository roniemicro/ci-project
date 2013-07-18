<?php
/**
 * @Author: Roni Kumar Saha
 * Date: 7/16/13
 * Time: 11:49 AM
 */

namespace Emicro\l10n\POMO;


class StringReader extends Reader {

    var $_str = '';

    function __construct($str = '') {
        parent::__construct();
        $this->_str = $str;
        $this->_pos = 0;
    }


    function read($bytes) {
        $data = $this->substr($this->_str, $this->_pos, $bytes);
        $this->_pos += $bytes;
        if ($this->strlen($this->_str) < $this->_pos) $this->_pos = $this->strlen($this->_str);
        return $data;
    }

    function seekto($pos) {
        $this->_pos = $pos;
        if ($this->strlen($this->_str) < $this->_pos) $this->_pos = $this->strlen($this->_str);
        return $this->_pos;
    }

    function length() {
        return $this->strlen($this->_str);
    }

    function read_all() {
        return $this->substr($this->_str, $this->_pos, $this->strlen($this->_str));
    }

}