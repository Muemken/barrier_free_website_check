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

include_once "html/footer.html";
echo '</body>';
echo '</html>';
