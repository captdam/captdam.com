<?php
	if (SHOW) {
		writeLog('String data output.');
		echo $BW->data['Data'];
	}
	else {
		$BW->data['TemplateMain'] = 'page';
		$BW->data['TemplateSub'] = 'object';
		writeLog('Template switched to: page->object.');
		$BW->useTemplate(false);
	}
?>