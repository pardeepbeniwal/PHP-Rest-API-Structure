<?php
include_once DIR_PATH.'config/MysqlConnection.php';

class REST extends MysqlConnection{
         
        public $_allow = array();
        public $_content_type = "application/json";
        public $_request = array();
         
        private $_method = "";      
        private $_code = 200;
        private $dbObject = '';
        public $dbConnectionObj = '';
        protected $loginUserInfo = array();

        public function __construct(){
        	$this->dbObject = MysqlConnection::GetInstance();
            $this->inputs();
        }
         
        public function getReferer(){
            return $_SERVER['HTTP_REFERER'];
        }
        
        private function getHeader(){
        	return apache_request_headers();
        }

        public function response($data,$status){
            $this->_code = ($status)?$status:200;
            $this->setHeaders();
            echo $data;
            exit;
        }
         
        private function getStatusMessage(){
            $status = unserialize(ERROR_MESSASGE);
            return ($status[$this->_code])?$status[$this->_code]:$status[500];
        }
         
        public function getRequestMethod(){
            return $_SERVER['REQUEST_METHOD'];
        }
         
        private function inputs(){
            switch($this->getRequestMethod()){
                case "POST": 
					$inputJSON = file_get_contents('php://input');					
                	$this->_request = $this->cleanInputs($this->getInputFromJson($inputJSON));  
                	$this->userValidate();      	 		
                    break;
                case "GET":
                case "DELETE":
                    $this->_request = $this->cleanInputs($_GET);
                    break;                
                default:
                    $this->response('',406);
                    break;
            }
        }       
         
        private function cleanInputs($data){
            $clean_input = array();
            if(is_array($data)){
                foreach($data as $k => $v){
                    $clean_input[$k] = $this->cleanInputs($v);
                }
            }else{
                if(get_magic_quotes_gpc()){
                    $data = trim(stripslashes($data));
                }
                $data = strip_tags($data);
                $clean_input = trim($data);
            }
            return $clean_input;
        }       
        
        private function setHeaders(){
            header("HTTP/1.1 ".$this->_code." ".$this->getStatusMessage());
            header("Content-Type:".$this->_content_type);
        }

        private function getInputFromJson($inputJSON){
        	 $input     = json_decode($inputJSON, true);
        	 if(is_array($input)){
        	 	return $input;
        	 }else {
        	 	$this->response(json_encode(array('error' => JSON_INVALID)),400);
        	 }
        }

        public function dbConnection(){
			 $this->dbConnectionObj  = $this->dbObject->createConnection();
			 return $this->dbConnectionObj;
        }

        protected function setDb($dbConnection){        	
        	$this->dbObject->setDatabase($dbConnection);
        	$this->dbConnection();
        }

        protected function pdoConnectionClose(){
        	$this->dbConnectionObj = null;
        	$this->connectionClose();
        }
        private function userValidate(){
        	$data = $this->_request;
        	if(isset($data['token']) && !empty($data['token'])){
        		require 'Encryption.php';
        		$encryptionObj = new Encryption();
        		$user_id   = $encryptionObj->getUserIdFromToken($data['token']);
        		if($user_id){
        			if(!$this->checkValidtoken($data['token'], $user_id)){
        				$this->response(json_encode(array('status' => 0, 'error' => TOKEN_INVALID)),200);
        			}
        		} else {
        			$this->response(json_encode(array('status' => 0, 'error' => TOKEN_INVALID)),200);
        		}
        	} else {
        		$this->response(json_encode(array('status' => 0, 'error' => TOKEN_MISSING)),200);
        	}        	
        }

        private function checkValidtoken($token, $user_id)
		{		
			$sessionId = 0;				
			try{ 
				$mysqlObj = $this->dbConnection();
				$result = $mysqlObj->query("select id from user_sessions where sessionId='$token' and userId=$user_id and is_login=0 limit 1");				
		        if ($result->fetchColumn() > 0) {
		        	$this->getUserInfo($user_id);
		           	$sessionId = 1;
		        } 
		    } catch(Exception $e){
		    	echo $e->getMessage();exit;
		    }
			return $sessionId;	
		}

		private function getUserInfo($user_id){
			try{ 
				$fetchAll = $this->dbConnectionObj->query("select * from users where id=$user_id limit 1");
				$this->loginUserInfo = $fetchAll->fetchAll(PDO::FETCH_ASSOC)[0];				
		    } catch(Exception $e){
		    	echo $e->getMessage();exit;
		    }
		}

		protected function json($data){
	        if(is_array($data)){
	            return json_encode($data);
	        } 
	    }
    }