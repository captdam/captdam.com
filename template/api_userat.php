<?php
	if ($BW->client['Username'][0] == '@') {
		http_response_code(401);
		writeLog('Not a user, AT dead.');
	}
	else {
		$BW->API['Username'] = $BW->client['Username'];
		writeLog('User AT. GET');
	}
?>
