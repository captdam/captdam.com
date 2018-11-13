<?php
	if (SHOW) {
		//Show HD from storage server
		if (array_key_exists('HD',$_GET)) {
			writeLog('HD data required. Go with object storage server.');
			
			$token = $BW->site['ObjStoToken'];
			$os = new BearwebObjectStorage($token,$BW->site['ObjStoExpire']);
			echo $os->getContent($BW->data['URL']);
			
			if ($token != $BW->site['ObjStoToken']) {
				writeLog('Object storage token changed.');
				$BW->database->updateObjectStorage($token);
			}
		}
		//Show thumb from local
		else {
			header('Content-Type: image/jpeg');
			echo $BW->data['Binary'];
			writeLog('Binary (thumb) data output.');
		}
	}
	else {
		$BW->data['TemplateMain'] = 'page';
		$BW->data['TemplateSub'] = 'object';
		writeLog('Template switched to: page->object.');
		$BW->useTemplate(false);
	}
?>
