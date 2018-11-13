<?php
	if (!isset($_GET['page']) || !checkRegex('URL',$_GET['page'])) {
		http_response_code(400);
		define('TEMPLATE_NOTEERROR',1);
		throw new BW_Error('Bad or undefined page.');
	}
	
	writeLog('Get info of page. URL = '.$_GET['page']);
	$page = $BW->database->getPageByURL($_GET['page']);
	
	if (!$page) {
		http_response_code(404);
		define('TEMPLATE_NOTEERROR',1);
		throw new BW_Error('No such page.');
	}
	
	if ($page['Author'] != $BW->client['Username'] && $BW->client['Group'] != '@Admin') {
		http_response_code(403);
		define('TEMPLATE_NOTEERROR',1);
		throw new BW_Error('No editing privilege.');
	}
	
	//Get sub work (add-on)
	if (isset($_GET['sub'])) {
		writeLog('Get sub work.');
		$BW->API['Work'] = $BW->database->getSubwork($_GET['page']);
	}
	//Get this work
	else {
		writeLog('Get main work.');
		$BW->API['Work'] = $page;
	}
?>
