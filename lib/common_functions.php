<?php
function pr($data){
	echo '<pre>';
	print_r($data);
	echo "</pre>";
}

function write_log($message){

	$myFile = '../../log/error_log.txt';		
	$fh = fopen($myFile, 'a');
	fwrite($fh, $message."\n");
	fclose($fh);	
}