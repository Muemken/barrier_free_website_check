<?php

/**
 * Description of Auth
 *
 * @author alex
 */
class auth {
    private $db;
    private $session_handler;

    public function __construct($sh, $db) {
        $this->db = $db;
        $this->session_handler = $sh;
        $uname = filter_input(INPUT_POST, 'uname', FILTER_SANITIZE_STRING);
        $pwd = filter_input(INPUT_POST, 'pwd', FILTER_SANITIZE_STRING);
        if(NULL != $uname && NULL != $pwd ) {
            $this->login($uname, $pwd);
        } else {
            echo file_get_contents('html/form_login.html', TRUE);
        }
    }

    public function login($uname, $pwd) {
        $verified = $this->db->db_login($uname, $pwd);
        echo $verified;
        if($verified) {
            $this->session_handler->set_login($uname);
            //session_regenerate_id(); //SID regenerieren, um Session Fixation zu verhindern
        } else {
            $this->session_handler->setError("Benutzername oder Passwort falsch");
        }
    }

    public function logout() {
        $this->session_handler->free();
    }
}
?>