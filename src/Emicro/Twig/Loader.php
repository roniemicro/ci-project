<?php
/**
 * @Author: Roni Kumar Saha
 *        Date: 7/17/13
 *        Time: 3:33 PM
 */

namespace Emicro\Twig;


use Emicro\Base\Controller;

class Loader extends \Twig_Environment
{

    /**
     * @var Controller
     */
    private $CI;

    public function __construct(\Twig_LoaderInterface $loader = NULL, $options = array())
    {
        parent::__construct($loader, $options);

        $this->CI = & get_instance();

        $this->initCustomFunctions();
        $this->initCustomFilters();
        $this->initCustomExtensions();

    }

    public function translate($value, $domain = FALSE)
    {
        return _t($value, $domain);
    }

    private function initCustomFunctions()
    {
        $this->addFunction(new \Twig_SimpleFunction('_t', array($this, 'translate')));
        $this->addFunction(new \Twig_SimpleFunction('nonce', 'nonce'));
        $this->addFunction(new \Twig_SimpleFunction('valid_nonce', 'valid_nonce'));
    }

    private function initCustomFilters()
    {
        $this->addFilter(new \Twig_SimpleFilter('localize', array($this, 'translate')));
    }

    private function initCustomExtensions()
    {
      //  $this->addExtension(new EzRbacTwigExtention());
    }

    /**
     * Render a template.
     *
     * {@inheritdoc }
     */
    public function render($name, array $context = array())
    {
        $context['__FILE__'] = $this->CI->getTwigPath($name);
        $context['APPPATH'] = realpath(APPPATH);
        $context['DIRECTORY_SEPARATOR'] = DIRECTORY_SEPARATOR;
        $name = $this->CI->getTwigTemplateName($name);

        return $this->loadTemplate($name)->render($context);
    }

    /**
     * Displays a template.
     *
     * {@inheritdoc }
     */
    public function display($name, array $context = array())
    {
        $context['__FILE__'] = $this->CI->getTwigPath($name);
        $context['APPPATH'] = realpath(APPPATH);
        $context['DIRECTORY_SEPARATOR'] = DIRECTORY_SEPARATOR;
        $name = $this->CI->getTwigTemplateName($name);

        $output = $this->loadTemplate($name)->render($context);
        $this->CI->output->append_output($output);
    }
}