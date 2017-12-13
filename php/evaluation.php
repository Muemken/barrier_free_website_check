<?php

/**
 * evaluations class with results of test.
 *
 * @author alexander
 */
class evaluation {

    var $result;

    function __construct($result) {
        $this->result = $result;
        if ($this->result == NULL) {
            $this->result = $_SESSION['result'];
        }
    }

    function show_result() {
        $evaluation = file_get_contents('html/evaluation.html', TRUE);
        $pattern = array('%length%','%yes%', '%no%', '%invalid%', '%notag%', '%skip%', '%result%');

        $result_array = $this->read_results();
        $result = 'Die Website ist ' . ($result_array[0] > 70 ? '' : 'nicht ') . ' barrierefrei!';
        array_push($result_array, $result);
        echo str_replace($pattern, $result_array, $evaluation);
    }

    function read_results() { // ++n:0i:0   start: 2 end: 5
        $start = strpos($this->result, 'n'); // in string looks like 'n:'
        $end = strpos($this->result, 'i', $start + 2); // in string looks like 'i:'

        $notag = substr($this->result, $start + 2, $end - $start - 2);
        $invalid = substr($this->result, $end + 2);
        $result = substr($this->result, 0, $start);
        $yes = 0;        
        $no = 0;
        $skip = 0;

        for ($i = 0; $i < strlen($result); ++$i) {
            if ($result[$i] == '+') {
                $yes ++;
            } else if ($result[$i] == '-') {
                $no ++;
            } else {
                $skip ++;
            }
        }
        $length = intval(strlen($result) + $invalid + $notag);

        if ($length == 0) {
            return array(0, 0, 0, 0, 0, 0);
        }
        return array($length, $yes = $yes / $length * 100 , $no / $length * 100, $invalid / $length * 100,
            $notag / $length * 100, $skip / $length * 100);
    }

}
