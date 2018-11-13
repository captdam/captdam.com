<?php
	if (!isset($_GET['username']) || !checkRegex('Username',$_GET['username'])) {
		http_response_code(400);
		define('TEMPLATE_NOTEERROR',1);
		throw new BW_Error('Bad username.');
	}
	
	writeLog('Get profile of user: '.$_GET['username']);
	$user = $BW->database->getUserByUsername($_GET['username']);
	
	if (!$user) {
		http_response_code(404);
		define('TEMPLATE_NOTEERROR',1);
		throw new BW_Error('No such user.');
	}
	
	$BW->API['Username'] = $user['Username'];
	$BW->API['Nickname'] = $user['Nickname'];
	$BW->API['Group'] = $user['Group'];
	$BW->API['LastActiveTime'] = $user['LastActiveTime'];
	$BW->API['RegisterTime'] = $user['RegisterTime'];
	$BW->API['Work'] = $BW->database->getWorkByUsername($user['Username']);
?>
