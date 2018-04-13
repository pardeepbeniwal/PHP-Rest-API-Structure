<?php
include_once '../../config/constant.php';
include_once DIR_PATH.'lib/common_functions.php';
include_once DIR_PATH.'lib/Validation.php';
require_once(DIR_PATH.'lib/Rest.php');
include 'Schedule.php';
class API extends Rest {
     
    public $data = "";   
    use Schedule; 
    use Validation; //class for validation
    public function __construct(){
          parent::__construct();          
	}

    public function processApi(){               
        $func = strtolower(trim(str_replace("/","",isset($_GET['func']) ? $_GET['func'] : '')));
        if((int)method_exists($this,$func) > 0){            
            $this->$func();
        }
        else {             
            $this->response(PAGE_NOT_FOUND,404);
        }
	}
   
   #test method
    private function test(){    
         if($this->getRequestMethod() != "POST"){
            $this->response('',406);
        }
        
        $errors = $this->requiredField(array('title','email'=>'email'), $this->_request);
        if(isset($errors) && empty($errors)){
            #$this->setDb('master');//for connection change            
            $conn = $this->dbConnectionObj;
            $result = $this->getUser($conn);            
            #$this->loginUserInfo /*loggedin user info here*/
        } else {
            $result['error'] = $errors[0];
            $result['status'] = 0;
        }        
        $this->response($this->json($result), 200);    
     }     
} 
    $api = new API;
    $api->processApi();
    #echo PHP_VERSION;