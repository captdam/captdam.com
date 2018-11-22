<?php
	//Check URL format
	if (!isset($_GET['page']) || !checkRegex('URL',$_GET['page'])) {
		http_response_code(400);
		define('TEMPLATE_NOTEERROR',1);
		throw new BW_Error('Bad or undefined page name.');
	}
	
	//Get page info
	writeLog('Modify page. URL = '.$_GET['page']);
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
	
	//Check input data format
	writeLog('Checking data.');
	foreach(
		['MIME','Title','Keywords','Description','Category','Author','TemplateMain','TemplateSub','Data','Copyright'] as $x
	) if (!array_key_exists($x,$_POST) || !is_string($_POST[$x])) {
		http_response_code(400);
		define('TEMPLATE_NOTEERROR',1);
		throw new BW_Error('Bad data: '.$x);
	}
	
	if (!isset($_POST['Status']) || !is_string($_POST['Status']) || strlen($_POST['Status']) != 1) {
		http_response_code(400);
		define('TEMPLATE_NOTEERROR',1);
		throw new BW_Error('Bad data: Status');
	}
	
	if (!isset($_POST['JSON']) || !is_string($_POST['JSON']) || !is_array(json_decode($_POST['JSON'],true))) {
		http_response_code(400);
		define('TEMPLATE_NOTEERROR',1);
		throw new BW_Error('Bad data: JSON');
	}
	
	if (!isset($_POST['Binary']) || !is_string($_POST['Binary']) || base64_decode($_POST['Binary'],true) === false) {
		http_response_code(400);
		define('TEMPLATE_NOTEERROR',1);
		throw new BW_Error('Bad data: Binary');
	}
	
	writeLog('Process data.');
	//Process binary data
	//1 - Process image resource
	if ($_POST['TemplateSub'] == 'externalimage') {
		$_POST['MIME'] = 'image/jpeg';
		
		//Convert image
		try {
			writeLog('Convert image.');
			$imageHD = new ImageProcess($_POST['Binary']);
			$imageThumb = clone $imageHD;
			
			$imageHeight = $imageHD->getInfo()['height'];
			$imageWidth = $imageHD->getInfo()['width'];
			
			if ($imageHeight > 1000 || $imageWidth > 1500)
				$imageThumb->resize(1500,1500*$imageHeight/$imageWidth);
			
			if (substr($_POST['Copyright'],0,10) != 'Reference=' && $imageWidth > 1500 && $imageHeight > 1000)
				$imageHD->addImage('./object/watermark.png',-550,-300);
			
			$imageHD = $imageHD->render(95,null);
			$imageThumb = $imageThumb->render(65,null);
		} catch(Exception $e) {
			http_response_code(500);
			define('TEMPLATE_NOTEERROR',1);
			throw new BW_Error('Cannot convert image: '.$e->getMessage());
		}
		
		//Upload to object storage server
		writeLog('Upload HD image to object storage server.');
		$token = $BW->site['ObjStoToken'];
		$os = new BearwebObjectStorage($token,$BW->site['ObjStoExpire']);
		$os->saveContent($_GET['page'],$imageHD);
		
		$_POST['Binary'] = $imageThumb;
		unset($imageThumb);
		unset($imageHD);
		
		if ($token != $BW->site['ObjStoToken']) {
			writeLog('Object storage token changed.');
			$BW->database->updateObjectStorage($token);
		}
	}
	
	//2 - XX things
	// else if () {...}
	
	//3 - By default
	else {
		/* ... */
	}
	
	//Update database
	writeLog('Update database.');
	if (isset($_GET['ide']))
		$BW->database->updatePageIDE($_GET['page'],$_POST);
	else
		$BW->database->updatePage($_GET['page'],$_POST);
	
	http_response_code(201);
?>
