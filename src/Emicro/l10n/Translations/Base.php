<?php
/**
 * @Author: Roni Kumar Saha
 * Date: 7/16/13
 * Time: 11:37 AM
 */
namespace Emicro\l10n\Translations;

use Emicro\l10n\Translations\Entry;

class Base {
    var $entries = array();
    var $headers = array();

    /**
     * Add entry to the PO structure
     *
     * @param object &$entry
     * @return bool true on success, false if the entry doesn't have a key
     */
    function add_entry($entry) {
        if (is_array($entry)) {
            $entry = new Entry($entry);
        }
        $key = $entry->key();
        if (false === $key) return false;
        $this->entries[$key] = &$entry;
        return true;
    }

    function add_entry_or_merge($entry) {
        if (is_array($entry)) {
            $entry = new Entry($entry);
        }
        $key = $entry->key();
        if (false === $key) return false;
        if (isset($this->entries[$key]))
            $this->entries[$key]->merge_with($entry);
        else
            $this->entries[$key] = &$entry;
        return true;
    }

    /**
     * Sets $header PO header to $value
     *
     * If the header already exists, it will be overwritten
     *
     * TODO: this should be out of this class, it is gettext specific
     *
     * @param string $header header name, without trailing :
     * @param string $value header value, without trailing \n
     */
    function set_header($header, $value) {
        $this->headers[$header] = $value;
    }

    function set_headers(&$headers) {
        foreach($headers as $header => $value) {
            $this->set_header($header, $value);
        }
    }

    function get_header($header) {
        return isset($this->headers[$header])? $this->headers[$header] : false;
    }

    function translate_entry(&$entry) {
        $key = $entry->key();
        return isset($this->entries[$key])? $this->entries[$key] : false;
    }

    function translate($singular, $context=null) {
        $entry = new Entry(array('singular' => $singular, 'context' => $context));
        $translated = $this->translate_entry($entry);
        return ($translated && !empty($translated->translations))? $translated->translations[0] : $singular;
    }

    /**
     * Given the number of items, returns the 0-based index of the plural form to use
     *
     * Here, in the base Translations class, the common logic for English is implemented:
     * 	0 if there is one element, 1 otherwise
     *
     * This function should be overrided by the sub-classes. For example MO/PO can derive the logic
     * from their headers.
     *
     * @param integer $count number of items
     * @return int
     */
    function select_plural_form($count) {
        return 1 == $count? 0 : 1;
    }

    function get_plural_forms_count() {
        return 2;
    }

    function translate_plural($singular, $plural, $count, $context = null) {
        $entry = new Entry(array('singular' => $singular, 'plural' => $plural, 'context' => $context));
        $translated = $this->translate_entry($entry);
        $index = $this->select_plural_form($count);
        $total_plural_forms = $this->get_plural_forms_count();
        if ($translated && 0 <= $index && $index < $total_plural_forms &&
            is_array($translated->translations) &&
            isset($translated->translations[$index]))
            return $translated->translations[$index];
        else
            return 1 == $count? $singular : $plural;
    }

    /**
     * Merge $other in the current object.
     *
     * @param Object &$other Another Translation object, whose translations will be merged in this one
     * @return void
     **/
    function merge_with(&$other) {
        foreach( $other->entries as $entry ) {
            $this->entries[$entry->key()] = $entry;
        }
    }

    function merge_originals_with(&$other) {
        foreach( $other->entries as $entry ) {
            if ( !isset( $this->entries[$entry->key()] ) )
                $this->entries[$entry->key()] = $entry;
            else
                $this->entries[$entry->key()]->merge_with($entry);
        }
    }
}