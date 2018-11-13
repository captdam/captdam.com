<?php
	if ($this->client['Username'][0] == '@') {
		http_response_code(401);
		define('TEMPLATE_NOTEERROR',1);
		throw new BW_Error('Client not login.');
	}
	
	$update = array();
	
	if (isset($_POST['password_old']) && isset($_POST['password_new'])) {
		if (
			checkRegex('Password',$_POST['password_old']) &&
			checkRegex('Password',$_POST['password_new']) &&
			$_POST['password_old'] == $this->client['Password']
		) {
			$update['Password'] = $_POST['password_new'];
			writeLog('User send new password: '.$_POST['password_new']);
		}
		else {
			http_response_code(400);
			define('TEMPLATE_NOTEERROR',1);
			throw new BW_Error('Wrong password or the format is wrong.');
		}
	}
	
	if (isset($_POST['nickname'])) {
		if (checkRegex('Nickname',$_POST['nickname'])) {
			$update['Nickname'] = $_POST['nickname'];
			writeLog('User send new nickname: '.$_POST['nickname']);
		}
		else {
			http_response_code(400);
			define('TEMPLATE_NOTEERROR',1);
			throw new BW_Error('Nickname bad format.');
		}
	}
	
	if (isset($_POST['email'])) {
		if (filter_var($_POST['email'],FILTER_VALIDATE_EMAIL)) {
			$update['Email'] = $_POST['email'];
			writeLog('User send new email: '.$_POST['email']);
		}
		else {
			http_response_code(400);
			define('TEMPLATE_NOTEERROR',1);
			throw new BW_Error('Email bad format.');
		}
	}
	
	if (isset($_POST['avatar'])) {
		try {
			$photo = new ImageProcess($_POST['avatar']);
			$photo->resize(200,200);
			$update['Photo'] = $photo->render(60);
			writeLog('User send new avatar: [Photo]');
		} catch(Exception $e) {
			http_response_code(415);
			define('TEMPLATE_NOTEERROR',1);
			throw new BW_Error('Bad avatar image.');
		}
	}
	
	$BW->database->updateUser(
		$this->client['Username'],
		isset($update['Password']) ? $update['Password'] : null,
		isset($update['Nickname']) ? $update['Nickname'] : null,
		isset($update['Email']) ? $update['Email'] : null,
		isset($update['Photo']) ? $update['Photo'] : null
	);
	writeLog('User info updated');
	http_response_code(201);
?>
