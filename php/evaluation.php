<?php

/**
 * evaluations class with results of test.
 *
 * @author alexander
 */
class evaluation {

    var $db;
    var $sh;
    var $result;

    function __construct($db, $sh) {
        $this->db = $db;
        $this->sh = $sh;
//        $this->sh->ste_state('') // TODO think about if it is needed or usefull to set state here
    }

    function show_result() {
        $evaluation = file_get_contents('html/evaluation.html', TRUE);

        $pattern = array('%length%','%yes%', '%yes_p%','%no%', '%no_p%', '%invalid%', 
            '%invalid_p%', '%notag%', '%notag_p%','%skip%','%skip_p%' ,'%result%');

        $result_array = $this->read_results();
        $result = 'Die Website ist ' . ($result_array[0] > 70 ? '' : 'nicht ') . ' barrierefrei!';

        array_push($result_array, $result);
        echo str_replace($pattern, $result_array, $evaluation);
    }

    function read_results() {
        $this->result = $this->db->results();

        $yes = 0;
        $no = 0;
        $skip = 0;
        $notag = 0;
        $invalid = 0;

        foreach ($this->result as $res) {
            if ('ja' == $res['result']) {
                $yes++;
            } else if ('nein' == $res['result']) {
                $no++;
            } else if ('skipped' == $res['result']) {
                $skip++;
            } else if ('no_picture' == $res['result']) {
                $invalid++;
            } else if ('' == $res['alt']) {
                $notag++; // here we can have duplicates, because this is not skipped for evaluation yet.
            }
        }
        $length = count($this->result) - $skip;
       
        $yes_p = $yes / $length * 100; 
        $no_p = $no / $length * 100;
        $skip_p = $skip / $length * 100;
        $invalid_p = $invalid / $length * 100;
        $notag_p = $notag / $length * 100;
        
        return array($length, $yes, $yes_p, $no, $no_p, $invalid, $invalid_p,
            $notag, $notag_p, $skip, $skip_p);

    }

}
