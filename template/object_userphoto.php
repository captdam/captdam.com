<?php
	if (!isset($BW->data['JSON']['fetch']) || !is_string($BW->data['JSON']['fetch']))
		throw new BW_Error('Fetch field undefined or invalid.');
	
	writeLog('Fetching photo of user: '.$_GET['username']);
	if (!isset($_GET['username']) || !checkRegex('Username',$_GET['username'])) {
		http_response_code(400);
		define('TEMPLATE_NOTEERROR',1);
		throw new BW_Error('Bad or undefined username.');
	}
	
	$user = $BW->database->getUserByUsername($_GET['username']);
	if (!$user) {
		http_response_code(404);
		define('TEMPLATE_NOTEERROR',1);
		throw new BW_Error('No such user.');
	}
	
	echo $user[$BW->data['JSON']['fetch']];
	writeLog('User data fetched.');
?>
