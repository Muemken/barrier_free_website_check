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
    }

    /*
     * login, if logout
     * logout, if login
     */
    public function decide() {
        if( !$this->session_handler->logged_in() ) {
            echo $this->login();
        } else {
            echo $this->logout();
        }
    }

    /*
    public function decide_str() {
        if( !$this->session_handler->logged_in() ) {
            return $this->login();
        } else {
            return $this->logout();
        }
    }
    */
    
    public function login() {
        $uname = filter_input(INPUT_POST, 'uname', FILTER_SANITIZE_STRING);
        $pwd = filter_input(INPUT_POST, 'pwd', FILTER_SANITIZE_STRING);
        if(NULL != $uname && NULL != $pwd ) {
            $this->login_user($uname, $pwd);
        } else {
            return file_get_contents('html/form_login.html', TRUE);
        }
    }

    public function login_user($uname, $pwd) {
        $verified = $this->db->db_login($uname, $pwd);
        echo $verified;     // ??
        if($verified) {
            $this->session_handler->set_login($uname);
            //session_regenerate_id(); //SID regenerieren, um Session Fixation zu verhindern
        } else {
            $this->session_handler->setError("Benutzername oder Passwort falsch");
        }
    }

    public function logout() {
        if( True == filter_input(INPUT_POST, 'logout', FILTER_SANITIZE_STRING) ) {
            $this->session_handler->free();
        } else {
            return file_get_contents('html/form_logout.html', TRUE);
        }
    }
}
?>
