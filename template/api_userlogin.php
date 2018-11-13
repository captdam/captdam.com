<?php
	//Check
	if (!isset($_POST['username']) || !isset($_POST['password'])) {
		http_response_code(400);
		define('TEMPLATE_NOTEERROR',1);
		throw new BW_Error('Username and/or password undefined.');
	}
	
	writeLog('User send: '.$_POST['username'].' / '.$_POST['password']);
	if (!checkRegex('Username',$_POST['username']) || !checkRegex('Password',$_POST['password'])) {
		http_response_code(400);
		define('TEMPLATE_NOTEERROR',1);
		throw new BW_Error('Username and/or password wrong format.');
	}
	
	//Get user info
	$user = $BW->database->getUserByUsername($_POST['username']);
	if (!$user) {
		http_response_code(404);
		define('TEMPLATE_NOTEERROR',1);
		throw new BW_Error('No such user.');
	}
	
	//Verify password
	if ($user['Password'] != $_POST['password']) {
		http_response_code(401);
		define('TEMPLATE_NOTEERROR',1);
		throw new BW_Error('Wrong Password.');
	}
	
	//Update
	writeLog('User login, username: '.$user['Username']);
	$BW->database->userActive($user['Username']);
	session_start();
	$_SESSION['Username'] = $user['Username'];
	session_write_close();
	
	http_response_code(201);
