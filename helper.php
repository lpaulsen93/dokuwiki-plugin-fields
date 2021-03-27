<?php
/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     lpaulsen93
 */

class helper_plugin_fields extends DokuWiki_Plugin {
    function ODTSetUserField(&$renderer, $name, $value) {
        if (!method_exists ($renderer, 'addUserField')) {
            $name = $this->_fieldsODTFilterUserFieldName($name);
            $renderer->fields[$name] = $value;
        } else {
            $renderer->addUserField($name, $value);
        }
    }

    function ODTDisplayUserField(&$renderer, $name) {
        if (!method_exists ($renderer, 'insertUserField')) {
            $name = $this->_fieldsODTFilterUserFieldName($name);
            if (array_key_exists($name, $renderer->fields)) {
                return '<text:user-field-get text:name="'.$name.'">'.$renderer->fields[$name].'</text:user-field-get>';
            }
        } else {
            $renderer->insertUserField($name);
        }
        return '';
    }
}
