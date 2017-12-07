<?php

include_once 'evaluation.php';

/**
 * testclass
 *
 * @author alexander
 */
class website_test {

    // url of site which has to be checked
    var $url;
    // array of $picture, which has 'alt' and 'url' information.
    var $pictures;
    // store results :: nr: nr of pictures; yes|no: .. index of vote ---> serialize needed.
    // Therefore the stuff has to be stored on server in a file (like a cookie).
    // For now store the information in three varaibles and give that to $_POST, please decide to change later.
    var $results; // +: positiv; -: negativ; s: skipped; last entry: nr of picture without alt tag ('n:'); pictures which could not be read : invalid ('i')

    function test_site() {
        $this->url = filter_input(INPUT_POST, 'url', FILTER_SANITIZE_URL);
//        echo '<br>URL: '.$this->url.'<br>';
        if (NULL != $this->url) {
            $it = -1;
            $ans = filter_input(INPUT_POST, 'ans', FILTER_SANITIZE_STRING);
            if (NULL != $ans) {
                $it = $this->next_it($ans, filter_input(INPUT_GET, 'it', FILTER_SANITIZE_NUMBER_INT));
            } else {
                $it = 0;
            }
            $this->read_site();
            $this->fill_result($ans, $it - 1);

            // can be done directly in read site, as long as we do not use cookies. Check timing if this maybe better
            $this->check_picture($it);
        } else {
            include_once 'html/form_get_url.html';
        }
    }

    function fill_result($ans, $it) {
        $results = filter_input(INPUT_POST, 'results', FILTER_SANITIZE_STRING);
        if ($results != NULL) {
            $this->results = $results;
        }

        if (NULL == $ans || $it < 0) { // in the beginning
            return;
        }

        if ('yes' == $ans) {
            $this->results[$it] = '+';
        } else if ('no' == $ans) {
            $this->results[$it] = '-';
        } else if ('back' == $ans || 'forward' == $ans) {
            if ('+' == $this->results[$it] || '-' == $this->results[$it]) {
                return;
            }
            $this->results[$it] = 's';
        }
        $_SESSION['result'] = $this->results;
    }

    function next_it($ans, $it) {
        // TODO, this has to be adapted if the form (html) changes. -> big crab.
        // maybe better use something like enum or global static array for that
        // this is also better, if one time the language should be switchable ;)
        if ('back' == $ans) {
            return $it == 0 ? $it :  --$it;
        }
        return ++$it;
    }

    function read_site() {
        $site_content = $this->content_of_url();
        $this->results = ""; // will be overwritten later from set results
//        if ('404' == $site_content) {
//            echo 'fail: cannot open url ' . $this->url;
//        }
        // PREG_SET_ORDER -> all matches for one occurence per array
        // because of that we can (as long as no cookies should be used, access directly
        // and havent got to go the way [store in array, choose, send]
        $regex = '/<img(.*)>/';
        preg_match_all($regex, $site_content, $match, PREG_SET_ORDER);

        $it = 0;
        $notag = 0;
        $invalid = 0;
        foreach ($match as $var) {
            $src = $this->find_tag($var, 'src');
            // if there is not src in that line, we can ignore (can happen, if the regex does not fit very well.
            if ($src == NULL) {
                continue;
            }

            $alt = $this->find_tag($var, 'alt');
            if ($alt == NULL) {
                // count, cause this is important to know
                $notag++;
                continue;
            }

            //TODO: test if this is a callable picture! -> invalid++ -> continue; 
            $absolute_path = $this->absolut_path($this->url, $src);
            if (!$this->is_path_valid($absolute_path)) {
                $invalid++;
                continue;
            }

            $this->results = $this->results . '.';

            $this->pictures[$it]['url'] = $absolute_path;
            $this->pictures[$it]['alt'] = $alt;
            $it++;
        }
        if (count($this->pictures) == 0) {
            echo 'Keine Bilder gefunden!';
        }
        $this->results = $this->results . 'n:' . $notag . 'i:' . $invalid;
    }

    /**
     * 
     * @param type $url
     * @param type $path
     * @return type
     */
    function absolut_path($url, $path) {
        if (substr($path, 0, 4) == "www." || substr($path, 0, 4) == "http") {
            return $path;
        }
        return $url . '/' . $path;
    }

    function find_tag($value, $tag) {
// More nicely (and faster) use regex. But as this will not work for any reason, i wrote 'find_tag_in_string'
//        $regex = '/src\s*=\s*("|')([^("|').]*)("|')((\s+\w*)|\s*>)/';
//        preg_match_all($regex, $value, $match, PREG_PATTERN_ORDER);
        if (is_string($value)) {
            return $this->find_tag_in_string($value, $tag);
        }
        if (is_array($value)) {
            foreach ($value as $var) {
                $result = $this->find_tag_in_string($var, $tag);
                if ($result != NULL) {
                    return $result;
                }
            }
        }
        return NULL;
    }

    /**
     * check if the path file could be readed.
     * @param type $path most likely to a picture
     * @return boolean
     */
    function is_path_valid($path) {
        // TODO fill!
        return TRUE;
    }

    function find_tag_in_string($string, $tag) {
        $index = strpos($string, $tag);
        if ($index == FALSE) {
            return NULL;
        }

        $quote = "'";
        $start = strpos($string, $quote, $index);
        if ($start == FALSE) {
            $quote = '"';
            $start = strpos($string, $quote, $index);
        }

        $end = strpos($string, $quote, $start + 1);
        if ($end == FALSE) {
            return NULL;
        }

        return substr($string, $start + 1, $end - $start - 1);
    }

    function check_picture($it) {
//        var_dump($this->pictures);
        if (count($this->pictures) > $it) {
            $picture = $this->pictures[$it];
//            var_dump($this->results);
            $this->call_check($picture, $it);
        } else {
            $eval = new evaluation($this->results);
            $eval->show_result();
        }
    }

    function call_check($picture, $pic_it) {
        $question = file_get_contents('html/question.html', TRUE);
        // TODO: make array global, add values for the buttons, to be stable against changes in html
        $pattern = array('%picture%', '%alt_text%', '%description%', '%it%', '%url%', '%results%');
        $result_array = array($picture['url'], 'testbild ' . $pic_it, $picture['alt'], $pic_it, $this->url, $this->results);

        echo str_replace($pattern, $result_array, $question);
    }

    /**
     * opens an url and check if this url is valid
     * @return type http code of response. (200-> o.k >400: failure)
     */
    function content_of_url() {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0); // get body, not header
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // no ssl needed
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // no ssl needed
        curl_setopt($ch, CURLOPT_FAILONERROR, 0); // fail directly if error
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // do not print the exec directly
        curl_setopt($ch, CURLOPT_URL, $this->url);

//        $site_content = '404';
//        if (curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
        $site_content = curl_exec($ch);
//        }

        curl_close($ch);
        return $site_content;
    }

    function response_url($url) {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // no ssl needed
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // no ssl needed
        curl_setopt($ch, CURLOPT_FAILONERROR, 0); // fail directly if error
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // do not print the exec directly
        curl_setopt($ch, CURLOPT_URL, $url);

        $response = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $response;
    }

}
