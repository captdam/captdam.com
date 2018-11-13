<?php
	if (!isset($_GET['page']) || !Bearweb_4::checkRegex('URL',$_GET['page'])) {
		$errorMessage = self::httpResponseCode(400,true);
		if (self::SHOWERROR) {
			$errorMessage .= ' Bearweb framework API error: Bad or undefined page name.';
		}
		throw new BW_RuntimeError($errorMessage);
	}
	//New comment
	if (isset($_POST['comments'])) {
		/*
		try {
			$SQL =  'SELECT  Comment, Time,';
			$SQL .= ' (SELECT Nickname FROM '.Bearweb_4::DB_USER.' A WHERE A.Username = B.Author) Author';
			$SQL .= ' FROM '.Bearweb_4::DB_USER.' B ';
			$SQL .= ' WHERE URL = :url';
			$commentSQL = $BW->database->prepare($SQL);
			$commentSQL->bindValue(':url',$_POST['comments']);
		} catch(PDOException $e) {
			$errorMessage = self::httpResponseCode(500,true);
			if (self::SHOWERROR) {
				$errorMessage .= ' Bearweb framework database query execution error: '.$e->getMessage().'.';
			}
			throw new BW_RuntimeError($errorMessage);
		}
		*/
	}
	//Get all comments
	try {
		/*
		$SQL =  'SELECT  Comment, Time,';
		$SQL .= ' (SELECT Nickname FROM '.Bearweb_4::DB_USER.' A WHERE A.Username = B.Username) Author';
		$SQL .= ' FROM '.Bearweb_4::DB_Comment.' B ';
		$SQL .= ' WHERE URL = :url';
		*/
		$SQL = 'UPDATE BW_Comment SET URL = \'123\' WHERE 1 = 2';
		$commentSQL = $BW->database->prepare($SQL);
		$commentSQL->bindValue(':url',$_GET['page']);
		$commentSQL->execute();
		//$comment = $commentSQL->fetchAll(PDO::FETCH_ASSOC);
		$commentSQL->closeCursor();
	} catch(PDOException $e) {
		$errorMessage = self::httpResponseCode(500,true);
		if (self::SHOWERROR) {
			$errorMessage .= ' Bearweb framework database query execution error: '.$e->getMessage().'.';
		}
		throw new BW_RuntimeError($errorMessage);
	}
	//$BW->data['Data'] = $comment;
?>
