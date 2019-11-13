<?php

#-----------------------------------------------------------
# ”FØ‹@”\
#-----------------------------------------------------------
function user_auth(){
	global $in;
	global $admin_id;
	global $md5_pw;
	
	session_start();
	$input_id = $in["input_id"];
	$input_pw = $in["input_pw"];
	if($input_id == $admin_id && md5($input_pw) == $md5_pw){
		$_SESSION["input_id"] = $input_id;
		$_SESSION["input_pw"] = $input_pw;
		$in["mode"] = "item";
	}
}
