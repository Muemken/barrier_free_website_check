<?php
session_start();

echo '<html lang="de">';
include_once "html/head.html";
echo '<body>';
include_once "html/header.html";

if (NULL != filter_input(INPUT_GET, 'site', FILTER_SANITIZE_STRING)) {
    include_once 'php/website_test.php';
    $site = filter_input(INPUT_GET, 'site', FILTER_SANITIZE_STRING);
    if ($site == 'test') {
        $test = new website_test();
        $test->test_site();
    }
    else if ($site == 'evaluation') {
//        include_once "html/evaluation.html";
        include_once 'php/evaluation.php';
        $eval = new evaluation(NULL);
        $eval->read_results();
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
