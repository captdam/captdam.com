<?php
	if (array_key_exists('NR',$_GET))
		define('SHOW',false);
	else
		define('SHOW',true);
	$BW->useTemplate();
?>
