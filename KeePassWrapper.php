<?php

require_once("vendor/autoload.php");
require_once("DatabaseFilter.php");
use \KeePassPHP\KeePassPHP as KeePassPHP;

class KeePassWrapper
{
    private $kdbxPath;
    private $indexPath;
    private $error = false;
    private $decryptionTime = 0;
    private $decrypted = false;

    private $dbIndex = array();

    private $db;

    public function __construct($file)
    {
        if(!$this->isSecure()){
            die("KeePassWrapper will not work if not accessed through HTTPS!!");
        }
        if(!file_exists($file)){
            $this->error = "Databasefile does not exist!";
        }
        $this->kdbxPath = $file;
        $this->indexPath = $this->kdbxPath . "_index.json";
        KeePassPHP::init(null,false);
        if(file_exists($this->indexPath)) {
            $this->loadIndexFromFile();
        }
    }


    public function getPasswordForEntry($entryUUID){
        if(!$this->decrypted){
            $this->error = "Password database is encrypted.";
            return false;
        }

        return $this->db->getPassword($entryUUID);
    }


    public function getIndex(){
        return $this->decrypted ? $this->getFreshIndex() : $this->dbIndex;
    }

    public function hasIndex(){
        return !empty($this->dbIndex);
    }

    private function getFreshIndex(){
        if(!$this->decrypted){
            $this->error = "Cannot get fresh index, because the database isn't unlocked.";
            return false;
        }
        $index = array();
        $filter = new DatabaseFilter();

        $index["Groups"] = $this->db->getGroups()[0]->toArray($filter);
        $index["CustomIcons"] = $this->db->toArray($filter)["CustomIcons"];
        $index["CreationTime"] = time();
        return $index;

    }

    public function saveIndexToFile(){
        if(!$this->decrypted){
            $this->error = "Cannot save index to file, because the database isn't unlocked.";
            return false;
        }

        $index = $this->getFreshIndex();

        $this->dbIndex = $index;
        $f = fopen($this->indexPath, "w");
        fwrite($f, json_encode($index));
        fclose($f);
        return true;
    }

    public function loadIndexFromFile(){
        if(!file_exists($this->indexPath)){
            $this->error = "Cannot load indexFile: Index file does not exist!";
            return false;
        }
        $file = file_get_contents($this->indexPath);
        try{
            $this->dbIndex = json_decode($file, TRUE);
            return true;
        }catch(\JsonSchema\Exception\JsonDecodingException $e){
            $this->error = "JsonDecoding failed";
            return false;
        }
    }

    public function unlockDatabase($password){
        $rustart = getrusage();
        $ckey = KeePassPHP::masterKey();
        KeePassPHP::addPassword($ckey, $password);
        $this->db = KeePassPHP::openDatabaseFile($this->kdbxPath, $ckey, $this->error);
        $ru = getrusage();
        $this->decryptionTime = $this->rutime($ru, $rustart, "utime");
        $this->decrypted = !$this->error;
        return !$this->error;
    }

    public function getLastError(){
        return $this->error;
    }

    public function getDecryptionTime(){
        return $this->decryptionTime/1000 . " seconds";
    }



    /* Private UTIL Functions */

    private function rutime($ru, $rus, $index) {
        return ($ru["ru_$index.tv_sec"]*1000 + intval($ru["ru_$index.tv_usec"]/1000))
            -  ($rus["ru_$index.tv_sec"]*1000 + intval($rus["ru_$index.tv_usec"]/1000));
    }

    private function isSecure() {
        if($_SERVER["SERVER_ADDR"] == "127.0.0.1") {
            return true;
        }
        return
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || $_SERVER['SERVER_PORT'] == 443;
    }
}