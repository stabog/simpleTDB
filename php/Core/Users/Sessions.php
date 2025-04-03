<?php
namespace SimpleUsers;

use SimpleTdb\TextDataBase as TDB;
use StringHelper as SH;

class Sessions {
    protected $sessDbName = 'sess';
    protected $visitsDbName = 'visits';
    protected $id = null;
    protected $dataSess = [];
    protected $dataVisit = [];

    public function __construct(){
        $this->dataSess = TDB::getInstance($this->sessDbName, "", "guid");
        $this->dataVisit = TDB::getInstance($this->visitsDbName);

        if (isset($_SESSION["sessId"])){
            $this->id = $_SESSION["sessId"];
        } else if (isset($_COOKIE["sessId"])){
            $this->id = $_COOKIE["sessId"];
        }
        
    }


    public function run($act='', $id='')
    {
        //echo $act;
        

        if ($act == ''){
            $this->checkSess();
        }
        else if ($act == 'clean'){
            $this->cleanSess ();
        }
        //echo  $this->id;
    }



    protected function checkSess()
    {
        if (!$this->getSess()) $this->setNewSess();
        $this->setSess();        
    }
    
    protected function setNewSess ()
    {
        
        $tobase[2] = $_SESSION["curUserId"] ?? "";
        $tobase[3] = $_SERVER["REMOTE_ADDR"];
        $tobase[4] = $_SERVER["HTTP_USER_AGENT"];
        
        $this->id = $this->dataSess->add($tobase);
        $visitinfo = [2 => $tobase[2], 3 => $this->id, 4 => "init"];
        $this->dataVisit->add($visitinfo);
    }

    protected function getSess()
    {
        if (!$this->id) return false;

        $info = $this->dataSess->get($this->id);
        if (!$info) return false;

        if (isset($_SESSION["curUserId"]) and $info[2] != $_SESSION["curUserId"]){

            $info[2] = $_SESSION["curUserId"];
            $this->dataSess->upd($this->id, $info);
            $visitinfo = [2 => $info[2], 3 => $info[0], 4 => "login"];
            $this->dataVisit->add($visitinfo);


        } else if (isset($info[2]) and $info[2] != ''){

            $_SESSION["curUserId"] = $info[2];
        }
        

        

        $this->setSess();
        return  true;
    }



    public function setSess ()
    {
        if (!isset($_SESSION["sessId"])){
            $_SESSION["sessId"] = $this->id;
        }
        if (!isset($_COOKIE["sessId"]) or $_COOKIE["sessId"] != $_SESSION["sessId"]){
            setcookie("sessId", $this->id, time() + 60 * 60 * 24 * 30);
        }
    }


    public function cleanSess ()
    {
        $info = $this->dataSess->get($this->id);
        if ($info){            
            $this->dataSess->del($this->id);
            $visitinfo = [2 => $info[2], 3 => $info[0], 4 => "logout"];
            $this->dataVisit->add($visitinfo);
        }
        
        
        if (isset($_SESSION["sessId"])){
            unset($_SESSION["sessId"]);
        }
        if (is_array($_SESSION)){
            foreach ($_SESSION as $key => $value){
                unset($_SESSION[$key]);
            }
        }

        if (isset($_COOKIE["sessId"])){
            setcookie('sessId', '', time() - 3600);
            unset($_COOKIE["sessId"]);
        }
        
        
    }
    
}