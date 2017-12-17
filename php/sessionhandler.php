<?php

/**
 * Description of SessionHandler
 *
 * @author alex
 */
class session_handler {

    private $referrer;

    public function __construct() {
        $this->referrer = basename($_SERVER["REQUEST_URI"]);
    }

    public function free() {
        setcookie(session_name(), '', time()-3600,'/');
        session_destroy();
    }

    public function set_login($username) {
        if ($username) {
            $_SESSION['user'] = $username;
        }
    }
    
    public function user() {
        return $_SESSION['user'];
    }

    public function logged_in() {
        if (isset($_SESSION['user']))
            return true;
        return false;
    }

    public function set_error($error, $class, $function, $line) {
        $e = '';
        if(isset($_SESSION['error'])){
            $e = $_SESSION['error'];
        }
        $_SESSION['error'] = $e.' & '.$error.'_'.$class.'::'.$function.'('.$line.')<br>';
    }

    public function get_error() {
        if (isset($_SESSION['error'])) {
            $error = $_SESSION['error'];
            unset($_SESSION['error']);
            return $error;
        }
        return '';
    }

    public function getReferrer() {
        return $this->referrer;
    }
    
    public function set_state( $state ) {
        $_SESSION['state'] = $state;
    }

    public function get_state() {
        if(isset($_SESSION['state'])){
            return $_SESSION['state'];
        }
        return NULL;
    }
}
?>