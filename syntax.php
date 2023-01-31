<?php
/**
 * Fields Plugin: Re-usable user fields
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Aurelien Bompard <aurelien@bompard.org>, LarsDW223
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_fields extends DokuWiki_Syntax_Plugin {
    /**
     * Constructor. Loads helper plugin.
     */
    public function __construct() {
        $this->fields_helper = plugin_load('helper','fields');
        $this->usecounter_helper = plugin_load('helper','usecounter');
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
    public function handle($match, $state, $pos, Doku_Handler $handler) {
        $match = substr($match,9,-2); //strip markup
        $extinfo = explode('=',$match);
        $field_name = $extinfo[0];
        if (count($extinfo) < 2) { // no value
            $field_value = '';
        } elseif (count($extinfo) == 2) {
            $field_value = $extinfo[1];
        } else { // value may contain equal signs
            $field_value = implode(array_slice($extinfo,1), '=');
        }
        return array($field_name, $field_value);
    }

    /**
     * Create output
     */
    public function render($format, Doku_Renderer $renderer, $data) {
        global $ID;
        list($field_name, $field_value) = $data;
        if ($field_value == '') { // no value -> get the field
            if ($format == 'xhtml' && isset($renderer->fields) 
                    && array_key_exists($field_name, $renderer->fields)) {
                $renderer->doc .= $renderer->fields[$field_name];
                return true;
            } elseif ($format == 'odt') {
                $renderer->doc .= $this->fields_helper->ODTDisplayUserField($renderer, $field_name);
                return true;
            }
        } else {
            // set field
            if ($format == 'xhtml') {
                if (!isset($renderer->fields)) {
                    $renderer->fields = array();
                }
                $renderer->fields[$field_name] = htmlentities($field_value);
                return true;
            } elseif ($format == 'odt') {
                if ($this->getConf('firstfielddefinitionwins')) {
                    if ($this->usecounter_helper && $this->usecounter_helper->amountOfUses('fields_'.$field_name) == 0) {
                        $this->fields_helper->ODTSetUserField($renderer, $field_name, $renderer->_xmlEntities($field_value));
                    }
                    
                    if ($this->usecounter_helper) {
                        $this->usecounter_helper->incUsageOf('fields_'.$field_name);
                    }
                } else {
                    $this->fields_helper->ODTSetUserField($renderer, $field_name, $renderer->_xmlEntities($field_value));
                }
                return true;
            }
        }
        return false;
    }

    function _fieldsODTFilterUserFieldName($name) {
        // keep only allowed chars in the name
        return preg_replace('/[^a-zA-Z0-9_.]/', '', $name);
    }
}

//Setup VIM: ex: et ts=4 fileencoding=utf-8 :
