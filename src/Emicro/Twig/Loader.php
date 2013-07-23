<?php
/**
 * @Author: Roni Kumar Saha
 *        Date: 7/17/13
 *        Time: 3:33 PM
 */

namespace Emicro\Twig;


class Loader extends \Twig_Environment
{

    public function __construct(\Twig_LoaderInterface $loader = NULL, $options = array())
    {
        parent::__construct($loader, array());

        $this->initCustomFunctions();
        $this->initCustomFilters();
    }

    public function translate($value, $domain = FALSE)
    {
        return _t($value, $domain);
    }

    private function initCustomFunctions()
    {
        $this->addFunction(new \Twig_SimpleFunction('_t', array($this, 'translate')));
    }

    private function initCustomFilters()
    {
        $this->addFilter(new \Twig_SimpleFilter('localize', array($this, 'translate')));
    }
}