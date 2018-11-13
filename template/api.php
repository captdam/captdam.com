<?php
	header('Content-Type: application/json');
	header('Cache-Control: no-cache, no-store, must-revalidate');
	$BW->API = array();
	
	try {
		writeLog('Process with API: '.$BW->data['TemplateSub']);
		$BW->useTemplate();
		
	} catch(BW_Error $e) {
		if (defined('TEMPLATE_NOTEERROR')) {
			writeLog('BW_Error found in template: '.$e->getMessage());
		}
		else {
			writeLog('BW_Error found in template: '.$e->getMessage(),true);
			if (http_response_code() == 200)
				http_response_code(500);
		}
		
		$BW->API['Error'] = $e->getMessage();
	}
	
	$BW->API['Request ID'] = TRANSACTIONID;
	$BW->API['Session ID'] = $BW->client['SID'];
	echo json_encode($BW->API);
	
	writeLog('API execute successfully. HTTP Code: '.http_response_code());
?>
