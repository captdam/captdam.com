<?php
	
	if (SHOW) {
		$file = './object/'.$BW->data['Data'];
		writeLog('Reading file: '.$file);
		
		if (!file_exists($file))
			throw new BW_Error('Cannot read file.');
		
		readfile($file);
		writeLog('File content printed.');
	}
	else {
		$BW->data['TemplateMain'] = 'page';
		$BW->data['TemplateSub'] = 'object';
		writeLog('Template switched to: page->object.');
		$BW->useTemplate(false);
	}
?>