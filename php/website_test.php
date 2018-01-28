<?php
set_time_limit(0);
include_once 'evaluation.php';

/**
 * testclass
 *
 * @author alexander
 */
class website_test {

    // url of site which has to be checked
    var $url;
    var $sh;
    var $db;

    public function __construct($sh, $db) {
        $this->sh = $sh;
        if ($this->sh->get_state() == NULL) {
            $this->sh->set_state('filldb');
        }
        $this->db = $db;
    }

    public function test_site() {
        $this->url = filter_input(INPUT_POST, 'url', FILTER_SANITIZE_URL);

        if (NULL != $this->url) {
            $it = -1;
            $ans = filter_input(INPUT_POST, 'ans', FILTER_SANITIZE_STRING);
            if (NULL != $ans) {
                $it = $this->next_it($ans, filter_input(INPUT_GET, 'it', FILTER_SANITIZE_NUMBER_INT));
            } else {
                // TODO make proper check if there is already a running test!! 
                // Otherwise this will always resetted, if one clicks on refresh or back in the browser!!!
                $this->sh->set_state('filldb');
                $it = 0;
            }

            if ($this->sh->get_state() == 'filldb') {
                $this->read_site();
            }

            if ($this->sh->get_state() == 'testside') {   
                $this->fill_result($ans, $it - 1); // more store result in DB
                $this->check_picture($it);
            }
        } else {
            include_once 'html/form_get_url.html';
        }
    }

    //TODO write unit test
    // TODO overwork
    public function fill_result($ans, $it) {
        if (NULL == $ans || $it < 0) { // in the beginning
            return;
        }
        $picture = $this->db->picture_with_id($it);

        if ('zurück' != $ans && 'weiter' != $ans) {
            $picture['result'] = $ans;
        }

        $this->db->update_picture($picture);
    }

    //TODO write unit test
    // TODO check if longer needed?
    public function next_it($ans, $it) {
        // TODO, this has to be adapted if the form (html) changes. -> big crab.
        // maybe better use something like enum or global static array for that
        // this is also better, if one time the language should be switchable ;)
        if ('back' == $ans) {
            // verringere iterator, falls nicht beim ersten Element 
            return $it == 0 ? $it : --$it;            
        }
        return ++$it;
    }

    //TODO write unit test
    public function get_links($url) {
        $result_urls = array();
        $site_content = $this->content_of_url($url);
        if ($site_content == NULL || $site_content == "") {
            return $result_urls;
        }

        $regex = '/<a(.*)>/';           // TODO find better regex
        // warum nicht '/<a href="   ????
        $match = NULL;
        preg_match_all($regex, $site_content, $match, PREG_SET_ORDER);
        foreach ($match as $var) {
            $src = $this->find_tag($var, 'href');
            if ($src == NULL || $src == "" || $src[0] == '#') {
                continue;
            }

            $absolute_path = $this->absolut_path($this->url, $src);  // this-> url ????

            if ($this->url_exists($absolute_path) &&
                    $this->is_path_valid($absolute_path, True, array('jpg', 'jpe', 'pdf', 'gif', 'png', 'doc'))) { /// ???
                array_push($result_urls, $absolute_path);
            }
            // ??? nochmal verstehen
        }

        return $result_urls;
    }

    //TODO write unit test
    private function get_pictures($url) {
        $site_content = $this->content_of_url($url);
        $pictures = array();

        //TODO check if content is valid?
        // PREG_SET_ORDER -> all matches for one occurence per array
        // because of that we can (as long as no cookies should be used, access directly
        // and havent got to go the way [store in array, choose, send]
        $regex = '/<img(.*)>/';
        preg_match_all($regex, $site_content, $match, PREG_SET_ORDER);

        foreach ($match as $var) {
            $src = $this->find_tag($var, 'src');
            // if there is not src in that line, we can ignore (can happen, if the regex does not fit very well.
            if ($src == NULL) {
                continue;
            }

            //TODO: test if this is a callable picture! -> invalid++ -> continue; 
            $absolute_path = $this->absolut_path($this->url, $src);
            if (!$this->is_path_valid($absolute_path)) {
                continue;
            }

            $alt = $this->find_tag($var, 'alt');
            if ($alt == NULL) {
                $alt = '';
            }

            array_push($pictures, array('path' => $absolute_path, 'alt' => $alt, 'result' => 'skipped'));
        }
        return $pictures;
    }

    private function merge_arrays($array1, $array2) {

        if (is_array($array2)) {
            // needed to avoid flaten input array
            $merged = array_map("unserialize", array_unique(array_map("serialize", array_merge($array1, $array2))));
        } else {
            array_push($array1, $array2);
            $merged = array_unique($array1);
        }

        return $merged;
    }

    public function read_site($rek_deep = 1) {
        //$rek_deep=0 inklusive alle unterseiten der Homepage
        //$rek_deep=1 iklusive aller unterseiten der unterseiten
        // ...
        $urls = array($this->url);
        $it = 0;
        $end_run = 0;
        $loop_counter = 0; // TODO
        while ($it <= $end_run) {
            if(array_key_exists($it, $urls)) {
                $urls = $this->merge_arrays($urls, $this->get_links($urls[$it]));
            }
            if($it == $end_run && $loop_counter < $rek_deep) {
                $end_run = max(array_keys($urls));
                $loop_counter++;
            }

            $it++;
        }

        $pictures = array();
        foreach ($urls as $url) {
            $pictures = $this->merge_arrays($pictures, $this->get_pictures($url));
        }

        $this->db->add_urls_to_db($urls);
        $this->db->add_pictures_to_db($pictures);

        $this->sh->set_state('testside');
    }

    public function absolut_path($url, $path) {
        // alternative ask starts with http, but i think this way is better, since the 
        // path has to be relativ to url, or absolute
        // regex taken from stackoverflow:
        // https://stackoverflow.com/questions/20015453/php-regex-preg-match-on-a-variable-containing-a-url
//        if (preg_match('~' . preg_quote($url, '~') . '~A', $path) > 0) {
//            return $path;
//        }
        if (substr($path, 0, 4) == "www" || substr($path, 0, 4) == "http") {
            return $path;
        }
        return $url . '/' . $path;
    }

    public function find_tag($value, $tag) {
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
     * @param type $startswithurl Only pathes from same side are valid (used for getting all internal links)
     * @param type $exclude pathes which ends up with any from exclude are not valid (used to not add pictures to link lits)
     * @return boolean
     */
    public function is_path_valid($path, $startswithurl = FALSE, $exclude = NULL) {
        if ($exclude != NULL) {
            if (in_array(substr($path, -3), $exclude)) {
                return false;
            }
        }
        $start = substr($path, 0, 4);
        if ($start != 'http' && $start != 'www.') {
            return false;
        }

        if ($startswithurl && substr($path, 0, strlen($this->url)) != $this->url) {
            return false;
        }

        return TRUE;
    }

    public function find_tag_in_string($string, $tag) {
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

    public function check_picture($it) {
        $picture = $this->db->picture_with_id($it);
        if ($picture == NULL) {
            $eval = new evaluation($this->db, $this->sh);
            $eval->show_result();
        } else {
            $this->call_check($picture, $it);
        }
    }

    public function call_check($picture, $pic_it) {
        $question = file_get_contents('html/question.html', TRUE);
        // TODO: make array global, add values for the buttons, to be stable against changes in html
        $pattern = array('%picture%', '%alt_text%', '%description%', '%it%', '%url%');
        $result_array = array($picture['path'], 'testbild ' . $pic_it, $picture['alt'], $pic_it, $this->url);

        echo str_replace($pattern, $result_array, $question);
    }

    /**
     * opens an url and check if this url is valid
     * @return type http code of response. (200-> o.k >400: failure)
     */
    public function content_of_url($url = NULL) {
        if ($url == NULL) {
            $url = $this->url;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0); // get body, not header
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // no ssl needed
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // no ssl needed
        curl_setopt($ch, CURLOPT_FAILONERROR, 0); // fail directly if error
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // do not print the exec directly
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); 
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $url);

//        $site_content = '404';
//        if (curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
        $site_content = curl_exec($ch);
//        }

        curl_close($ch);
        return $site_content;
    }

    public function url_exists($url) {
        return curl_init($url);
    }

    public function response_url($url) {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // no ssl needed
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // no ssl needed
        curl_setopt($ch, CURLOPT_FAILONERROR, 0); // fail directly if error
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // do not print the exec directly
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_URL, $url);

        $response = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        echo '<br>respone: ' . $response . '<br>';
        curl_close($ch);
        return $response;
    }

}
