<?php

require_once("vendor/autoload.php");
require_once("DatabaseFilterForIndex.php");
require_once("DatabaseFilterWithEverything.php");
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

    public function getEntry($entryUUID){
        if(!$this->decrypted){
            $this->error = "Cannot get entry. Database is not unlocked.";
            return false;
        }
        $filter = new DatabaseFilterWithEverything();
        $database =$this->db->toArray($filter);

        $path_ = $this->recursive_array_search($entryUUID, $database);

        if(!$path_){
            $this->error = "Entry does not exist in database!";
            return false;
        }

        //Convert ["Group"][xx]["Group"][yy] to an array("Group", xx, "Group", yy).
        $path = array();
        foreach(explode('[', $path_) as $item){
            $val = explode(']',$item)[0];
            if(is_numeric($val)){
                $path[] = intval($val);
            }else {
                $path[] = $val;
            }
        }

        //Array filter removes all empty strings and null values
        $path = array_filter($path,function($value) {return !is_null($value) && $value !== '';});

        return $this->array_nested_value($database, $path)["StringFields"];

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
        $filter = new DatabaseFilterForIndex();

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

        if($this->decrypted){
            $this->saveIndexToFile(); //Refreshed index;
        }

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


    //Thanks Pieter De Schepper on Stackoverflow :)
    private function recursive_array_search($needle, $haystack, $currentKey = '') {
        foreach($haystack as $key=>$value) {
            if (is_array($value)) {
                $nextKey = $this->recursive_array_search($needle,$value, $currentKey . '[' . $key . ']');
                if ($nextKey) {
                    return $nextKey;
                }
            }
            else if($value==$needle) {
                return is_numeric($key) ? $currentKey . '[' .$key . ']' : $currentKey;
            }
        }
        return false;
    }

    private function array_nested_value($array, $path) {
        $temp = &$array;

        foreach($path as $key) {
            $temp =& $temp[$key];
        }
        return $temp;
    }

}