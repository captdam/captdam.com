<?php
	if (!isset($_GET['page']) || !checkRegex('URL',$_GET['page'])) {
		http_response_code(400);
		define('TEMPLATE_NOTEERROR',1);
		throw new BW_Error('Bad or undefined page name.');
	}
	
	writeLog('Creating page: '.$_GET['page']);
	$BW->database->createPage($_GET['page'],array(
		'Title'		=> $_GET['page'],
		'Author'	=> $BW->client['Username'],
		'Category'	=> isset($_GET['ide']) ? ucfirst(explode('/',$_GET['page'])[0]) : '@Alone',
		'MIME'		=> isset($_GET['ide']) ? 'text/html' : 'text/plain',
		'Status'	=> 'P'
	));
	
	$BW->API['Data'] = $BW->database->getPageByURL($_GET['page']);
	
	writeLog('Page created.');
	http_response_code(201);
?>
