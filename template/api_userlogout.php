<?php
	if ($BW->client['Username'][0] == '@') {
		http_response_code(401);
		define('TEMPLATE_NOTEERROR',1);
		throw new BW_Error('Not login.');
	}

	writeLog('User '.$BW->client['Username'].' logout.');
	session_start();
	$_SESSION['Username'] = '@Visitor';
	session_write_close();
	
	http_response_code(410);
?>
