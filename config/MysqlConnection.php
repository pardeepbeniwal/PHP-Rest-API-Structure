<?php
/** Created By Pardeep Beniwal
* Dated on:22/03/2018
*Database connection file
*/
include 'Database.php';
class MysqlConnection extends DATABASE_CONFIG{

	private static $instance = null;
	private $host;
	private $login;
	private $password;
	private $database;
	public $connection = null;
	private function __construct()
	{
	  $this->host = $this->default['host'];
	  $this->login = $this->default['login'];
	  $this->password = $this->default['password'];
	  $this->database = $this->default['database'];	 
	}

	public static function GetInstance()
	{
		if(!self::$instance)
		{
			self::$instance = new MysqlConnection();
		}
		return self::$instance;
	}

	public function createConnection(){	  	  
	  try{
		  $this->connection = new PDO("mysql:host=$this->host;dbname=$this->database", $this->login, $this->password);	
		  if (!$this->connection) {
            echo 'Cannot connect to database server';
            exit;
        }     
		  return $this->connection;
		} catch(PDOException $e){ 
			write_log(date('Y-m-d H:i:s').'--createConnection--'.$e->getMessage());
			echo $e->getMessage();
			exit;
		}
	}

	public function connectionClose(){
		$this->connection = null;
	}

	public function setDatabase($default='default'){
	   $config 	       = $this->$default;
	   $this->host     = $config['host'];
	   $this->login    = $config['login'];
	   $this->password = $config['password'];
	   $this->database = $config['database'];
	}
}

?>