<?php
	if (SHOW) {
		writeLog('Print Binary data.');
		echo $BW->data['Binary'];
	}
	else {
		$BW->data['TemplateMain'] = 'page';
		$BW->data['TemplateSub'] = 'object';
		writeLog('Template switched to: page->object.');
		$BW->useTemplate(false);
	}
?>