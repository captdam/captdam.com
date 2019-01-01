<?php
	if (!isset($_POST['username']) || !isset($_POST['nickname']) || !isset($_POST['password'])) {
		http_response_code(400);
		define('TEMPLATE_NOTEERROR',1);
		throw new BW_Error('Username, password and/or nickname undefined.');
	}
	
	if (!checkRegex('Username',$_POST['username']) || !checkRegex('Nickname',$_POST['nickname']) || !checkRegex('Password',$_POST['password'])) {
		http_response_code(400);
		define('TEMPLATE_NOTEERROR',1);
		throw new BW_Error('Username, password and/or nickname wrong format.');
	}
	
	writeLog('New user: Nickname '.$_POST['nickname'].' (Username '.$_POST['username'].'). IP: '.$BW->client['IP']);
	if(!$BW->database->newUser(
		$_POST['username'],
		$_POST['nickname'],
		'@User',
		$_POST['password'],
		$BW->client['IP'],
		fopen('./object/te.jpg','r')
	)) {
		http_response_code(400);
		define('TEMPLATE_NOTEERROR',1);
		throw new BW_Error('Username has been used.');
	}
	
	http_response_code(201);
?>
