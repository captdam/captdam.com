<?php
	if (!isset($_GET['cmd']) || !isset($_GET['arg'])) {
		http_response_code(400);
		define('TEMPLATE_NOTEERROR',1);
		throw new BW_Error('Bad or undefined cmd/arg.');
	}
	
	writeLog('Cmd: '.$_GET['cmd'].' / Arg: '.$_GET['arg']);
	$token = $BW->site['ObjStoToken'];
	$os = new BearwebObjectStorage($token,$BW->site['ObjStoExpire']);
			
	switch ($_GET['cmd']) {
		case 'list':
			$BW->API['Data'] = $os->getList();
			break;
		case 'get':
			$BW->API['Data'] = $os->getContent($_GET['arg']);
			break;
		case 'delete':
			$os->deleteFile($_GET['arg']);
			break;
		default:
			http_response_code(501);
			define('TEMPLATE_NOTEERROR',1);
			throw new BW_Error('Cmd not support.');
	}
	
	if ($token != $BW->site['ObjStoToken']) {
		writeLog('Object storage token changed.');
		$BW->database->updateObjectStorage($token);
	}
?>