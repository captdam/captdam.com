<?php
	//Check URL format
	if (!isset($_GET['page']) || !checkRegex('URL',$_GET['page'])) {
		http_response_code(400);
		define('TEMPLATE_NOTEERROR',1);
		throw new BW_Error('Bad or undefined page name.');
	}
	
	//Get page info
	writeLog('Delete page. URL = '.$_GET['page']);
	$page = $BW->database->getPageByURL($_GET['page']);
	if (!$page) {
		http_response_code(404);
		define('TEMPLATE_NOTEERROR',1);
		throw new BW_Error('No such page.');
	}
	
	//Check if author or admin, otherwise 403
	if ($page['Author'] != $BW->client['Username'] && $BW->client['Group'] != '@Admin') {
		http_response_code(403);
		define('TEMPLATE_NOTEERROR',1);
		throw new BW_Error('No editing privilege.');
	}
	
	//Check data modified
	if (!isset($_POST['Etag']) || $_POST['Etag'] != $page['LastModify']) {
		http_response_code(401);
		define('TEMPLATE_NOTEERROR',1);
		throw new BW_Error('Data was changed.');
	}
	
	//Delete on object storage server
	writeLog('Delete from object storage server.');
	$token = $BW->site['ObjStoToken'];
	$os = new BearwebObjectStorage($token,$BW->site['ObjStoExpire']);
	$os->deleteFile($_GET['page']);
	
	if ($token != $BW->site['ObjStoToken']) {
		writeLog('Object storage token changed.');
		$BW->database->updateObjectStorage($token);
	}
	
	//Delete from sitemap
	writeLog('Delete from sitemap.');
	$BW->database->deletePage($_GET['page']);
	
	//Done
	writeLog('Page deleted.');
	http_response_code(410);
?>
