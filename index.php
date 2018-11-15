<?php
	//Generate transaction id
	define('TRANSACTIONID',uniqid(time(),true));
	
	header('Cache-Control: private, max-age=3600');
	header('B-Powered-By: Bearweb 5.0');
	header('B-Request-ID: '.TRANSACTIONID);
	date_default_timezone_set('UTC');
	
	//Include the Bearweb framwwork
	require_once './config.php';
	require_once './bearweb.class.php';
	require_once './database.class.php';
	require_once './util.php';
	require_once './conoha.class.php';
	
	//Setup error handler
	set_error_handler(function($errNo,$errStr,$errFile,$errLine){
		if (error_reporting() == 0) {
			return false;
		}
		throw new ErrorException($errStr,0,$errNo,$errFile,$errLine);
	});
	class BW_Error extends Exception {}
	
	//Process page
	writeLog('Job start!');
	ob_start();
	try {
		$BW = new Bearweb();
		$BW->ini();
		$BW->useTemplate(false);
	} catch(BW_Error $e) { #Handle error
		ob_clean();
		ob_start();
		$BW->useErrorTemplate($e->getMessage());
	} catch(Exception $e) { #Unexcepted error
		http_response_code(500);
		writeLog('[EXCEPTION]'.$e->getMessage(),true);
		ob_clean();
		ob_start();
		writeLog('BW error debug info: '.print_r($e,true),true);
		$BW->useErrorTemplate('[EXCEPTION] Bearweb internal error.');
	}
	
	//Record request result
	$timeUsed = (microtime(true)-$_SERVER['REQUEST_TIME_FLOAT']).'ms';
	try {
		$BW->endRequest($timeUsed);
	} catch (Exception $e) {
		writeLog('[WARNING]Session shows username but not in db.');
	}
	writeLog('Job done! '.$timeUsed);
	
	//Write log to file system
	function writeLog($string,$err=false) {
		$text  = '['.date('y-m-d H:i:s').']';
		$text .= '['.TRANSACTIONID.']';
		$text .= $err ? '[ERROR]' : '';
		$text .= $string."\n";
		
		$file = './log/'.date('y-m-d').'.log';
		for ($i = 0; $i < 5; $i++) { #Retry 5 times if files system busy
			if (file_put_contents($file,$text,FILE_APPEND))
				break;
			usleep(1000);
		}
		
		if (!$err) #Write error to error log file
			return;
		$file = './log/'.date('y-m-d').'-error.log';
		for ($i = 0; $i < 5; $i++) { #Retry 5 times if files system busy
			if (file_put_contents($file,$text,FILE_APPEND))
				break;
			usleep(1000);
		}
	}
?>
