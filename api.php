<?php
require_once("KeePassWrapper.php");
include ("settings.php");
function checkValues() {
    foreach (func_get_args() as $param) {
        if(!isset($_POST[$param]) || $_POST[$param] == ""){
            return false;
        }
    }
    return true;
}
if (!isset($_POST['action'])) {
    die("NO ACTION!!");
}

switch($_POST['action']) {
    case "refreshIndex": echo refreshIndex(); break;
    case "getPassword": echo getPassword(); break;
    case "getEntry": echo getEntry(); break;
    //Default
    default: die("Unknown action...");
}
die("");

function refreshIndex(){
    global $kdbxPath;
    if(checkValues("masterPassword")){
        $db = new KeePassWrapper($kdbxPath);
        if($db->getLastError()){
            return $db->getLastError();
        }

        if($db->unlockDatabase($_POST['masterPassword']) && $db->saveIndexToFile()){
            return "OK!";
        }else{
            return $db->getLastError();
        }

    }else{
        return "No master password given!";
    }
}

function getPassword(){
    global $kdbxPath;
    if(checkValues("masterPassword", "entryUUID")){
        $db = new KeePassWrapper($kdbxPath);
        if($db->getLastError()){
            return "ERROR: ". $db->getLastError();
        }
        if($db->unlockDatabase($_POST['masterPassword'])){
            if($pass = $db->getPasswordForEntry($_POST['entryUUID'])){
                return $pass;
            }
        }
        return "ERROR: ". $db->getLastError();
    }else{
        return "ERROR: No master password or entry UUID given!";
    }
}

function getEntry(){
    global $kdbxPath;
    if(checkValues("masterPassword", "entryUUID")){
        $db = new KeePassWrapper($kdbxPath);
        if($db->getLastError()){
            return "ERROR: ". $db->getLastError();
        }
        if($db->unlockDatabase($_POST['masterPassword'])){
            if($entry = $db->getEntry($_POST['entryUUID'])){
                return json_encode($entry);
            }
        }
        return "ERROR: ". $db->getLastError();
    }else{
        return "ERROR: No master password or entry UUID given!";
    }
}