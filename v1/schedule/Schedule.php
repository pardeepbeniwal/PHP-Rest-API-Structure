<?php

trait Schedule{

	public function getUser($conn){
		$result = $conn->query('select id,first_name from users where first_name != "" limit 10');
		$res = array();
        if ($result->fetchColumn() > 0) {
           foreach ($result->fetchAll(PDO::FETCH_OBJ) as $row) {
                $res[$row->id] = array('id' => $row->id, 'name' => $row->first_name);
            }
        } 
        return $res;
	}
}