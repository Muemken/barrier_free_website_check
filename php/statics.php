<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of statics
 *
 * @author linda
 */
class statics {
    
    function __construct() {
        
    }

    function db_host() {
        return 'localhost';
    }

    function db_pwd() {
        return '';
    }

    function db_user() {
        return 'root';
    }

    function db_name() {
        return 'piksl';
    }

    function user_db() {
        return 'user';
    }
    
    function replace_uml_in_content($content) {
        $FIND = array('ß','ä','ö','ü','Ä','Ö','Ü');
        $REPLACE = array('&szlig;','&auml;','&ouml;','&uuml;','&Auml;','&Ouml;','&Uuml;');
        str_replace($FIND, $REPLACE, $content);
        return $content;
    }

}