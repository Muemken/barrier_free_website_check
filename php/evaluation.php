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

        $pattern = array('%length%', '%yes%', '%no%', '%invalid%', '%notag%', '%skip%', 
            '%yes_p%','%no_p%', '%invalid_p%', '%notag_p%', '%skip_p%',            
            '%yes_s%', '%no_s%', '%invalid_s%', '%notag_s%', '%skip_s%',
            '%result%', '%hidden%', '%pictures_list%');

        $result_array = $this->read_results();
        $result = 'Die Website ist ' . ($result_array[0] > 70 ? '' : 'nicht ') . ' barrierefrei!';

        array_push($result_array, $result);

        $it = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING); // TODO richtiges get holen!
        array_push($result_array, $it ? 'not_' : '');

        array_push($result_array, $this->get_pictures());

        echo str_replace($pattern, $result_array, $evaluation);
    }

    function get_pictures() {
        $it = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);
        $results = array();
        switch ($it) {
            case 'yes_list':
                $results = $this->db->results_for_bar('ja');
                break;
            case 'no_list':
                $results = $this->db->results_for_bar('nein');
                break;
            case 'skip_list':
                $results = $this->db->results_for_bar('skipped');
                break;
            case 'notag_list':
                $results = $this->db->results_for_bar('');
                break;
            case 'invalid_list':
                $results = $this->db->results_for_bar('Kein Bild');
                break;
        }

        $result_string = '';
        foreach ($results as $pic) {
            $result_string = $result_string . '<img src="' . $pic['path'] . '" />';
        }
//        echo $result_string;
        return $result_string;
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
            } else if ('Kein Bild' == $res['result']) {
                $invalid++;
            } else if ('' == $res['alt']) {
                $notag++; // here we can have duplicates, because this is not skipped for evaluation yet.
            }
        }
        //$length = count($this->result) - $skip;  ///warum?
        $length = count($this->result);

        $yes_p = round($yes / $length * 100, 2);
        $no_p = round($no / $length * 100, 2);
        $skip_p = round($skip / $length * 100, 2);
        $invalid_p = round($invalid / $length * 100, 2);
        $notag_p = round($notag / $length * 100, 2);

        return array($length, $yes, $no, $invalid, $notag, $skip,
            $yes_p, $no_p, $invalid_p, $notag_p, $skip_p,
            min($yes_p + 15, 100), min($no_p + 15, 100), min($invalid_p + 15, 100),
            min($notag_p + 15, 100), min($skip_p + 15, 100));
    }

}
