<?php
/**
 * @Author: Roni Kumar Saha
 * Date: 7/17/13
 * Time: 3:33 PM
 */

namespace Emicro\Twig;


class Loader extends \Twig_Environment{
      public function __construct(\Twig_LoaderInterface $loader = null, $options = array())
      {
          parent::__construct($loader , $options);
          $this->initCustomFunctions();
      }
    private function initCustomFunctions()
    {
        $function = new \Twig_SimpleFunction('_t', function ($value, $domain=false) {
            return _t($value, $domain);
        });

        $this->addFunction($function);
    }
}