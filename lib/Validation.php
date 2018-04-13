<?php
/* Created by Pardeep Beniwal
 * dated on : 26/03/2018
 */
trait Validation{

	/*
	* format of field
	*array('title', 'number' => 'number field','email'=>'email field name',)
	*/	
	public function requiredField($fieldName = array(), $fieldData = array()){
		$errors = array();
		foreach ($fieldName as $key => $value) {
			if(!isset($fieldData[$value]) || trim($fieldData[$value]) == ''){
				$errors[] = ucfirst($value).' is required.';
			} else if($key === 'email' && !filter_var($fieldData[$value], FILTER_VALIDATE_EMAIL)){
				$errors[] = ucfirst($value).' is not a valid email address.';
			} else if($key === 'number' && !is_numeric($fieldData[$value])){
				$errors[] = ucfirst($value).' should be numeric type.';
			}
		}		
		 return $errors;		
	}
}