<?php

namespace Emicro\Base;

use Emicro\l10n\POMO\MO;
use Emicro\l10n\Translations\NOOP;

class Lang extends \CI_Lang {

    protected $languages = array(
        'en' => 'english',
        'fr' => 'french',
    );

    // special URIs (not localized)
    protected $special = array ();

    // where to redirect if no language in URI
    protected $uri;
    protected $default_uri;
    protected $lang_code;


    /**************************************************/


    function __construct()
    {
        parent::__construct();

        global $CFG;
        global $URI;
        global $RTR;

        $this->uri = $URI->uri_string();
        $this->default_uri = $RTR->default_controller;

        $uri_segment = $this->get_uri_lang($this->uri);
        $this->lang_code = $uri_segment['lang'] ;

        $url_ok = false;
        if ((!empty($this->lang_code)) && (array_key_exists($this->lang_code, $this->languages)))
        {
            $language = $this->languages[$this->lang_code];
            $CFG->set_item('language', $language);
            $url_ok = true;
        }

        if ((!$url_ok) && (!$this->is_special($uri_segment['parts'][0]))) // special URI -> no redirect
        {
            // set default language
            $CFG->set_item('language', $this->languages[$this->default_lang()]);

            $uri = (!empty($this->uri)) ? $this->uri: $this->default_uri;
            $uri = ($uri[0] != '/') ? '/'.$uri : $uri;
            $new_url = $CFG->config['base_url'].$this->default_lang().$uri;

            header("Location: " . $new_url, TRUE, 302);
            exit;
        }
    }



    // get current language
    // ex: return 'en' if language in CI config is 'english' 
    function lang()
    {
        global $CFG;
        $language = $CFG->item('language');

        $lang = array_search($language, $this->languages);
        if ($lang)
        {
            return $lang;
        }

        return NULL;    // this should not happen
    }


    function is_special($lang_code)
    {
        if ((!empty($lang_code)) && (in_array($lang_code, $this->special)))
            return TRUE;
        else
            return FALSE;
    }


    function switch_uri($lang,$uri="")
    {
        if($uri!=""){   //Uri Provided
            return $lang.'/'.$uri;
        }
        if ((!empty($this->uri)) && (array_key_exists($lang, $this->languages)))
        {

            if ($uri_segment = $this->get_uri_lang($this->uri))
            {
                $uri_segment['parts'][0] = $lang;
                $uri = implode('/',$uri_segment['parts']);
            }
            else
            {
                $uri = $lang.'/'.$this->uri;
            }
        }

        return $uri;
    }

    //check if the language exists
    //when true returns an array with lang abbreviation + rest
    function get_uri_lang($uri = '')
    {
        if (!empty($uri))
        {
            $uri = ($uri[0] == '/') ? substr($uri, 1): $uri;

            $uri_expl = explode('/', $uri, 2);
            $uri_segment['lang'] = NULL;
            $uri_segment['parts'] = $uri_expl;

            if (array_key_exists($uri_expl[0], $this->languages))
            {
                $uri_segment['lang'] = $uri_expl[0];
            }
            return $uri_segment;
        }
        else
            return FALSE;
    }


    // default language: first element of $this->languages
    function default_lang()
    {
        $browser_lang = !empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? strtok(strip_tags($_SERVER['HTTP_ACCEPT_LANGUAGE']), ',') : '';
        $browser_lang = substr($browser_lang, 0,2);
        return (array_key_exists($browser_lang, $this->languages)) ? $browser_lang: 'en';
    }


    // add language segment to $uri (if appropriate)
    function localized($uri)
    {
        if (!empty($uri))
        {
            $uri_segment = $this->get_uri_lang($uri);
            if (!$uri_segment['lang'])
            {

                if ((!$this->is_special($uri_segment['parts'][0])) && (!preg_match('/(.+)\.[a-zA-Z0-9]{2,4}$/', $uri)))
                {
                    $uri = '/'.$this->lang() . '/' . $uri;
                }
            }
        }
        return $uri;
    }

    /**
     * Load a language file
     *
     * @access    public
     *
     * @param        mixed     the name of the language file to be loaded. Can be an array
     * @param        string    the language (english, etc.)
     * @param        bool      return loaded array of translations
     * @param        bool      add suffix to $langfile
     * @param        string    alternative path to look for language file
     * @param string $langfile
     * @param string $idiom
     * @param bool   $return
     * @param bool   $add_suffix
     * @param string $alt_path
     *
     * @return    mixed
     */
    function load($langfile = '', $idiom = '', $return = FALSE, $add_suffix = TRUE, $alt_path = '')
    {
        $ret = $this->load_text_domain($langfile, $idiom);
        if($ret == false){
            return parent::load($langfile, $idiom, $return, $add_suffix, $alt_path);
        }

        return $ret;
    }

    // --------------------------------------------------------------------

    /**
     * Fetch a single line of text from the language array
     *
     * @access	public
     * @param	string	$line	the language line
     * @return	string
     */
    function line($line = '', $domain = false)
    {
        if($domain == false){
            $value = ($line == '' OR ! isset($this->language[$line])) ? FALSE : $this->language[$line];
        }else{
            $value = $this->translate($line, $domain);
        }

        // Because killer robots like unicorns!
       /* if ($value === FALSE)
        {
            log_message('error', 'Could not find the language line "'.$line.'"');
        }*/

        return $value;
    }

    private function load_text_domain($domain = 'default', $idiom = '')
    {
        if ($idiom == '') {
            $idiom = $this->lang_code;
        } else {
            $idiom = array_search($idiom, $this->languages);
        }

        $modir  = realpath(APPPATH . 'language' . DIRECTORY_SEPARATOR . "mo") . DIRECTORY_SEPARATOR;
        $mofile = $modir . "{$domain}_{$idiom}.mo";

        if (!is_readable($mofile)) return FALSE;

        $mo = new MO();
        if (!$mo->import_from_file($mofile)) return FALSE;

        if (isset($this->is_loaded[$domain]))
            $mo->merge_with($this->is_loaded[$domain]);

        return $this->is_loaded[$domain] = &$mo;

    }

    function translate( $text, $domain = 'default' ) {
        $translations = $this->get_translations_for_domain( $domain );
        return $translations->translate( $text );
    }

    function get_translations_for_domain( $domain ) {
        if ( !isset( $this->is_loaded[$domain] ) ) {
            $this->is_loaded[$domain] = new NOOP();
        }
        return $this->is_loaded[$domain];
    }
}

// END MY_Lang Class

/* End of file MY_Lang.php */
/* Location: ./application/core/MY_Lang.php */  