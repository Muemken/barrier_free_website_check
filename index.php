<?php
session_start();
ini_set('max_execution_time', 60);   // Standard 30 
error_reporting(E_ALL);  // show all errors 

echo '<html lang="de">';
include_once "html/head.html";
echo '<body>';


/**
 * Description of work_on_page
 *
 * @author alex
 */
//echo __CLASS__.'::'.__FUNCTION__.' ('.__LINE__.')<br>';

include_once './php/sessionhandler.php';
include_once './php/db_functions.php';
include_once './php/auth.php';
include_once './php/statics.php';

$sh = new session_handler();

//$db = new db($sh, true); // wieso true?
$db = new db($sh);
$db->db_connect();
$auth = new auth($sh, $db);


include_once 'html/header.html';
$auth->decide();
//$header = file_get_contents('html/header.html', TRUE);
//$pattern = array('%login%');
//$result_array = array($auth->decide_str());
//echo str_replace($pattern, $result_array, $header);



if (NULL != filter_input(INPUT_GET, 'site', FILTER_SANITIZE_STRING)) {
    include_once 'php/website_test.php';
    $site = filter_input(INPUT_GET, 'site', FILTER_SANITIZE_STRING);
    if ($site == 'test') {
        $test = new website_test($sh, $db);
        $test->test_site();
    }
    else if ($site == 'evaluation') {
        include_once 'php/evaluation.php';
        $eval = new evaluation($db, $sh);
        $eval->show_result();
    }
    else if ($site == 'home') {
        include_once "html/home.html";
    }
    else {
        echo('404 Site not found!');
    }
} else {
    include_once "html/home.html";
}



//$action = filter_input(INPUT_GET, 'action');
// 
//switch ($action) {
//    case 'yes_list':
//        include "html/evaluation.html";
//        include "html/evaluation_list.html";
//        //echo "yes_list";
//        break;
//    case 'no_list':
//        include "html/evaluation_list.html";
//        //echo "no_list";
//        break;
//    case 'skip_list':
//        include "html/evaluation_list.html";
//        //echo "skip_list";
//        break;
//    case 'notag_list':
//        include "html/evaluation_list.html";
//        //echo "notag_list";
//        break;
//    case 'invalid_list':
//        include "html/evaluation_list.html";
//        //echo "invalid_list";
//        break;
//}

include_once "html/footer.html";
echo '</body>';
echo '</html>';

?>

