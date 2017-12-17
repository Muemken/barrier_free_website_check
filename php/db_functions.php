<?php

error_reporting(-1);
ini_set('display_errors', 1);

/**
 * Description of dbLogin
 *
 * @author alex
 */
class db {

    private $db;
    private $sh;
    private $statics;
    private $print_failure;

    public function __construct($sh, $p = false) {
        $this->sh = $sh;
        $this->print_failure = $p;
        $this->statics = new statics();
    }

    public function db_connect() {
        $dsn = 'mysql:dbname=' . $this->statics->db_name() . ';host=' . $this->statics->db_host();
        $user = $this->statics->db_user();
        $password = $this->statics->db_pwd();

        try {
            $options = array(PDO::ATTR_AUTOCOMMIT => FALSE);
            $this->db = new PDO($dsn, $user, $password, $options);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $ex) {
            if ($this->print_failure)
                echo 'Connection failed: ' . $ex->getMessage();
        }
    }

    public function db_login($name, $pwd) {
        try {
            $statement = $this->db->prepare("SELECT * FROM `user` WHERE `name` LIKE ?");
            if (!$statement->execute(array($name))) {
                if ($this->print_failure)
                    echo "<br>SQL Error <br />";
                return false;
            }
            if ($statement->rowCount() > 1) {
                if ($this->print_failure)
                    echo '<br>Check die Datenbank, da scheinen doppelte Eintraege zu existieren :O<br>';
                return false;
            }

            return password_verify($pwd, $statement->fetch()['pwd']);
        } catch (Exception $ex) {
            if ($this->print_failure)
                echo 'login failed: ' . $ex->getMessage();
        }
    }

    public function update_picture($picture) {
        try {
            $this->db->beginTransaction();
            $statement = $this->db->prepare("UPDATE `pictures` SET `result` = :result WHERE `pictures`.`id` = :id ");
            $statement->bindParam(':id', $picture['id'], PDO::PARAM_INT);
            $statement->bindParam(':result', $picture['result'], PDO::PARAM_STR);
            $statement->execute();
            $this->db->commit();
        } catch (PDOException $e) {
            echo "Failed to get DB handle: " . $e->getMessage() . "\n";
        }
    }

    public function add_pictures_to_db($pictures) {
        try {
            $this->db->beginTransaction();
            $st_truncate = $this->db->prepare("TRUNCATE `pictures`");
            $st_truncate->execute();
            $this->db->commit();

            $this->db->beginTransaction();
            $id = 0;
            $picture = $pictures[$id];
            $path = $picture['path'];
            $alt = $picture['alt'];
            $result = $picture['result'];
            $statement = $this->db->prepare("INSERT INTO `pictures`(`id`, `path`, `alt`, `result`) VALUES( :id, :path, :alt, :result )");
            $statement->bindParam(':id', $id, PDO::PARAM_INT);
            $statement->bindParam(':path', $path, PDO::PARAM_STR);
            $statement->bindParam(':alt', $alt, PDO::PARAM_STR);
            $statement->bindParam(':result', $result, PDO::PARAM_STR);

            foreach ($pictures as $picture) {
                try {
                    $path = $picture['path'];
                    $alt = $picture['alt'];
                    $result = $picture['result'];
                    $statement->execute();
                    $id++;
                } catch (PDOException $e) {
//                    // most likely we have a dupliacte entry, do not echo in general
                }
            }
            $this->db->commit();
        } catch (PDOException $e) {
            echo "Failed to get DB handle: " . $e->getMessage() . "\n";
        }
    }

    public function picture_with_id($id) {
        try {
            $statement = $this->db->prepare("SELECT * FROM `pictures` WHERE `id` LIKE ?");
            if (!$statement->execute(array($id))) {
                if ($this->print_failure)
                    echo "<br>SQL Error <br />";
                return NULL;
            }

            return $statement->fetch();
        } catch (Exception $ex) {
            if ($this->print_failure)
                echo 'login failed: ' . $ex->getMessage();
        }
    }

    public function add_urls_to_db($url) {
        try {
            $this->db->beginTransaction();
            $statement = $this->db->prepare("TRUNCATE `links`");
            $statement->execute();
            $this->db->commit();

            $this->db->beginTransaction();
            $id = 0;
            $path = $url[$id];
            $statement = $this->db->prepare("INSERT INTO `links`(`id`, `path`) VALUES( :id, :path )");
            $statement->bindParam(':id', $id, PDO::PARAM_INT);
            $statement->bindParam(':path', $path, PDO::PARAM_STR);

            foreach ($url as $path) {
                try {
                    $statement->execute();
                    $id++;
                } catch (PDOException $e) {
//                    // most likely we have a dupliacte entry, do not echo in general
                }
            }
            $this->db->commit();
        } catch (PDOException $e) {
            echo "Failed to get DB handle: " . $e->getMessage() . "\n";
        }
    }

    public function url_with_id($id) {
        try {
            $statement = $this->db->prepare("SELECT * FROM `links` WHERE `id` LIKE ?");
            if (!$statement->execute(array($id))) {
                if ($this->print_failure)
                    echo "<br>SQL Error <br />";
                return NULL;
            }

            return $statement->fetch()['path'];
        } catch (Exception $ex) {
            if ($this->print_failure)
                echo 'login failed: ' . $ex->getMessage();
        }
    }

    public function results() {
        try {
            $statement = $this->db->prepare("SELECT * FROM `pictures`");
            if (!$statement->execute()) {
                if ($this->print_failure)
                    echo "<br>SQL Error <br />";
                return NULL;
            }
            $result = array();
            while ($row = $statement->fetch()) {
                array_push($result, $row);
            }
            return $result;
        } catch (Exception $ex) {
            if ($this->print_failure)
                echo 'login failed: ' . $ex->getMessage();
        }
    }

}

?>