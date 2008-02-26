<?php
/**
 * Fields Plugin: Re-usable user fields
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Aurelien Bompard <aurelien@bompard.org>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_fields extends DokuWiki_Syntax_Plugin {

    /**
     * return some info
     */
    function getInfo(){
        return confToHash(dirname(__FILE__).'/info.txt');
    }

    /**
     * What kind of syntax are we?
     */
    function getType(){
        return 'substition';
    }

    /**
     * Where to sort in?
     */
    function getSort(){
        return 319; // Before image detection, which uses {{...}} and is 320
    }


    /**
     * Connect pattern to lexer
     */
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('{{fields>.+?}}',$mode,'plugin_fields');
    }

    /**
     * Handle the match
     */
    function handle($match, $state, $pos, &$handler){
        $match = substr($match,9,-2); //strip markup
        $extinfo = explode('=',$match);
        $field_name = $extinfo[0];
        if (count($extinfo) < 2) { // no value
            $field_value = '';
        } elseif (count($field) == 2) {
            $field_value = $extinfo[1];
        } else { // value may contain equal signs
            $field_value = implode(array_slice($extinfo,1), '=');
        }
        return array($field_name, $field_value);
    }

    /**
     * Create output
     */
    function render($format, &$renderer, $data) {
        global $ID;
        list($field_name, $field_value) = $data;
        if ($field_value == '') { // no value -> get the field
            if ($format == 'xhtml' && isset($renderer->fields) 
                    && array_key_exists($field_name, $renderer->fields)) {
                $renderer->doc .= $renderer->fields[$field_name];
                return true;
            } elseif ($format == 'odt') {
                $renderer->_odtInsertUserField($field_name);
                return true;
            }
        } else {
            // set field
            if ($format == 'xhtml') {
                if (!isset($renderer->fields)) {
                    $renderer->fields = array();
                }
                $renderer->fields[$field_name] = $field_value;
                return true;
            } elseif ($format == 'odt') {
                $renderer->_odtAddUserField($field_name, $field_value);
                return true;
            }
        }
        return false;
    }

}

//Setup VIM: ex: et ts=4 enc=utf-8 :
