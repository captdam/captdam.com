<?php
	/**=========================================================**\
	|                          Bearweb 5                          |
	|            A lightweight PHP MySQL web framework            |
	\**=========================================================**/

	class Bearweb {

		//Bearweb framework-use variables
		protected $URL;		#URL of current page (request)
		protected $database;	#Database connection interface
		protected $site;	#Site infomation from database
		protected $client;	#Client infomation
		protected $data;	#page datas from database

		
		//Constructor function
		function __construct() {
		}

		//Print error page (using the HTML page template)
		public function useErrorTemplate($errorMessage) {
			writeLog('Error found, now handing by error template: '.$errorMessage);
			$this->data = array(
				'URL'		=> '',
				'MIME'		=> 'text/html',
				'Title'		=> 'An error has been detected',
				'Keywords'	=> '',
				'Description'	=> 'Bearweb framework error page',
				'Category'	=> 'Error',
				'Author'	=> '@Bearweb',
				'TemplateMain'	=> 'page',
				'TemplateSub'	=> 'error',
				'Data'		=> $errorMessage,
				'Binary'	=> '',
				'JSON'		=> array(),
				'CreateTime'	=> '1000-01-01 00:00:00',
				'LastModify'	=> '1000-01-01 00:00:00',
				'Version'	=> 0,
				'Status'	=> 'S',
				'Copyright'	=> 'All rights reserved'
			);
			try {
				$this->useTemplate(false);
			} catch(Exception $e) {
				writeLog('Fail to execute error template. Print in plain text.',true);
				echo $errorMessage;
				exit;
			}
			writeLog('Error template executed!');
		}

		//Using template file
		public function useTemplate($sub=true) {
			//Get template file
			$template = $sub ? 
				($this->data['TemplateMain'].'_'.$this->data['TemplateSub']) : 
				$this->data['TemplateMain'];
			writeLog('Using template: '.$template);
			$templateFile = './template/'.$template.'.php';
			
			//In case template file missing
			if (!file_exists($templateFile)) {
				http_response_code(500);
				writeLog('Template file is missing.',true);
				throw new BW_Error( DEBUGMODE ?
					('Bearweb framework server error: Fail to load template file. Template file: '.$template) :
					'Bearweb framework server-side error'
				);
			}
			
			//Execute template
			try {
				global $BW;
				include $templateFile;
			//Error found in template
			/*
			What to do in template
			1 - Write log with writeLog('description') for critical steps
			2 - If you need to throw an error (cause by client, ex, bad request)
				http_response_code(default:500);
				define('TEMPLATE_NOTEERROR','whatever');
				throw new BW_Error('description');
			3 - If you need to throw an error (cause by server, ex, external server fail)
				http_response_code(default:500);
				throw new BW_Error('description');
			4 - When you call some method, such as database operation
				You do not need to use try/catch, error will be catch here
			*/
			} catch(BW_Error $e) {
				/*
				NOTICE: BW_ERROR
				If the error is throw in sub-template, the error will be re-catch in
				main-template. So, do not process the exception while sub-template,
				just pass it to mian-template.
				*/
				if ($sub)
					throw new BW_Error($e->getMessage());
				
				if (defined('TEMPLATE_NOTEERROR')) {
					writeLog('BW_Error found in template: '.$e->getMessage());
				}
				else {
					writeLog('BW_Error found in template: '.$e->getMessage(),true);
					if (http_response_code() == 200)
						http_response_code(500);
				}
				
				throw new BW_Error( DEBUGMODE ?
					('Bearweb framework template error: error occured when execute template: '.$e->getMessage()) :
					'Bearweb framework template error'
				);
			}
			writeLog('Template: '.$template.' executed!');
		}
		
		function endRequest($time) {
			$this->database->logRequestEnd(
				http_response_code(),
				$time,
				TRANSACTIONID,
				$this->client['SID']
			);
		}
		
		//Inilization
		function ini() {
			$this->smartURL();
			$this->connectDatabase();
			$this->getSiteConfig();
			$this->getClientInfo();
			$this->getData();
			$this->processData();
		}

		//Get and trim the URL
		protected function smartURL() {
			$url = trim($_GET[APACHE_PARA]," \t\n\r\0\x0B\/");
			writeLog('Checking request URL. The request URL is: /'.$url);
			
			if (!checkRegex('URL',$url)) {
				http_response_code(400);
				writeLog('Request URL is invalid.');
				throw new BW_Error('Bearweb framework client error: Request URL contains invalid character.');
			}
			
			$this->URL = $url;
			writeLog('Request URL is valid!');
		}

		//Connect to database
		protected function connectDatabase() {
			writeLog('Connecting to database.');
			
			try {
				$this->database = new BearwebDatabase();
			} catch(BW_Error $e) {
				http_response_code(500);
				writeLog($e->getMessage(),true);
				throw new BW_Error( DEBUGMODE ?
					'Bearweb framework database error: Cannot connect to database.' :
					'Bearweb framework server-side error'
				);
			}
			
			writeLog('Database connected.');
		}

		//Get site setting from config database
		protected function getSiteConfig() {
			writeLog('Getting site config.');
			
			//Get site configs from db
			try {
				$config = $this->database->getSiteConfig();
			} catch(BW_Error $e) {
				http_response_code(500);
				writeLog($e->getMessage(),true);
				throw new BW_Error( DEBUGMODE ?
					('Bearweb framework database error: '.$e->getMessage()) :
					'Bearweb framework server-side error'
				);
			}
			
			//Checking mandatory config
			$mandatoryFlags = ['Closed'];
			foreach($mandatoryFlags as $x)
				if (!array_key_exists($x,$config)) {
					http_response_code(500);
					writeLog('Missing flag: '.$x,true);
					throw new BW_Error( DEBUGMODE ?
						('Bearweb framework server error: Missing flag '.$x) :
						'Bearweb framework server-side error'
					);
				}
			
			//Site closed, accessable by localhost only
			if ($config['Closed'] && $_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
				http_response_code(503);
				writeLog('Site closed.');
				echo 'Server closed: '.$config['Closed'];
				exit;
			}
			
			$this->site = $config;
			writeLog('Site config loaded.');
		}

		//Get client info
		protected function getClientInfo() {
			session_start();
			$SID = session_id();
			writeLog('Getting client info. IP: '.$_SERVER['REMOTE_ADDR'].' @ '.$SID);
			
			//Returned user
			if(isset($_SESSION['Username'])) {
				writeLog('Session data found: '.$_SESSION['Username']);
				
				//Returned visitor
				if ($_SESSION['Username'][0] == '@') {
					/*
					Search engine should not remember session cookie,
					if a request states a 'returned bot', it is highly likes
					a people with a fake http-user-agent. In this case, changing
					it to @Visitor instead of @Bot.
					*/
					$_SESSION['Username'] = '@Visitor';
					session_write_close();
					
					$this->client = array(
						'Username' => '@Visitor',
						'Group' => '@Visitor',
						'IP' => $_SERVER['REMOTE_ADDR'],
						'SID' => $SID
					);
					writeLog('Client info determined: @Visitor (returned visitor).');
				}
				
				//Returned user
				else {
					try {
						$user = $this->database->getUserByUsername($_SESSION['Username']);
					} catch(BW_Error $e) {
						$_SESSION['Username'] = '@Visitor';
						session_write_close();
						
						http_response_code(500);
						writeLog($e->getMessage(),true);
						throw new BW_Error( DEBUGMODE ?
							('Bearweb framework database error: '.$e->getMessage()) :
							'Bearweb framework server-side error'
						);
					}
					
					if (!$user) { #Session shows username, but cannot find in database, trade as a visitor. This shouldn't happen
						$_SESSION['Username'] = '@Visitor';
						session_write_close();
						
						$this->client = array(
							'Username' => '@Visitor',
							'Group' => '@Visitor',
							'IP' => $_SERVER['REMOTE_ADDR'],
							'SID' => $SID
						);
						writeLog('[WARNING]Session shows username but not in db.');
						writeLog('Client info determined: @Visitor.');
					}
					else {
						$_SESSION['Username'] = $user['Username']; #Re-alias
						session_write_close();
						
						$this->client = $user;
						writeLog('Client info determined: '.$user['Username']);
						
						$removeKey = ['Photo'];
						foreach ($removeKey as $x)
							unset($this->client[$x]);
						$this->client['IP'] = $_SERVER['REMOTE_ADDR'];
						$this->client['SID'] = $SID;
						
						//Update user last active time
						try {
							$this->database->userActive($user['Username']);
							writeLog('User info updated.');
						} catch(BW_Error $e) {
							http_response_code(500);
							writeLog($e->getMessage(),true);
							throw new BW_Error( DEBUGMODE ?
								('Bearweb framework database error: '.$e->getMessage()) :
								'Bearweb framework server-side error'
							);
						}
					}
				}
			}
			
			//New visitor
			else {
				writeLog('Session data not found, new client.');
				
				//User-agent shows it is bot
				if (isset($_SERVER['HTTP_USER_AGENT']) && checkRegex('SearchEngine',$_SERVER['HTTP_USER_AGENT'])) {
					$_SESSION['Username'] = '@Bot';
					session_write_close();
					
					$this->client = array(
						'Username' => '@Bot',
						'Group' => '@Bot',
						'IP' => $_SERVER['REMOTE_ADDR'],
						'SID' => $SID
					);
					writeLog('Client info determined: @BOT.');
					return;
				}
				
				//New user
				else {
					$_SESSION['Username'] = '@Visitor';
					session_write_close();
					
					$this->client = array(
						'Username' => '@Visitor',
						'Group' => '@Visitor',
						'IP' => $_SERVER['REMOTE_ADDR'],
						'SID' => $SID
					);
					writeLog('Client info determined: @Visitor (new visitor).');
				}
			}
			
			//Write user request log
			try {
				$this->database->logRequest(
					$this->URL,
					$this->client,
					TRANSACTIONID
				);
				writeLog('Request recorded.');
			} catch(BW_Error $e) {
				throw new BW_Error( DEBUGMODE ?
					('Bearweb framework database error: '.$e->getMessage()) :
					'Bearweb framework server-side error'
				);
				writeLog('Fail to log request: '.$e->getMessage(),true);
			}
			
			writeLog('Client info processed.');
		}

		//Get all data for a page from the database
		protected function getData() {
			writeLog('Loading page data.');
			
			try {
				$this->data = $this->database->getPageByURL($this->URL);
			} catch(BW_Error $e) {
				http_response_code(500);
				writeLog($e->getMessage(),true);
				throw new BW_Error( DEBUGMODE ?
					('Bearweb framework database error: '.$e->getMessage()) :
					'Bearweb framework server-side error'
				);
			}
			
			if(!$this->data) {
				http_response_code($this->client['Username'] == '@Bot' ? 410 : 404);
				writeLog('Page not found.');
				throw new BW_Error('Bearweb framework client error: Page not found.');
			}
			
			writeLog('Page data fetched.');
		}

		//Process page data
		protected function processData() {
			writeLog('Processing page. Status: '.$this->data['Status']);
			
			//Determine flag
			switch($this->data['Status']) {
			  case 'R': #Page removed perm
			  case 'r': #Page removed temp
				if (
					!isset($this->data['JSON']['redirect']) ||
					!is_string($this->data['JSON']['redirect'])
				) {
					http_response_code(500);
					writeLog('Redirect URL undefined.',true);
					throw new BW_Error( DEBUGMODE ?
						'Bearweb framework server error: Redirect URL undefined, redirect fail.' :
						'Bearweb framework server-side error'
					);
				}
				
				http_response_code($this->data['Status'] == 'R' ? 301 : 302);
				header('Location: /'.$this->data['JSON']['redirect']);
				writeLog('Page redirect to: '.$this->data['JSON']['redirect']);
				exit;
			
			  case 'A': #Auth need (privilege)
				if (
					!isset($this->data['JSON']['whitelist']) ||
					!is_array($this->data['JSON']['whitelist'])
				) {
					http_response_code(500);
					writeLog('Whitelist undefined.',true);
					throw new BW_Error( DEBUGMODE ?
						'Bearweb framework server error: Whitelist undefined, fail to varify privilege.' :
						'Bearweb framework server-side error'
					);
				}
				
				if (
					$this->client['Group'] != '@Admin' &&
					$this->client['Username'] != $this->data['Author'] &&
					!in_array($this->client['Username'],$this->data['JSON']['whitelist']) &&
					!in_array($this->client['Group'],$this->data['JSON']['whitelist'])
				) { #Open to admin group, author and those has privilege
					http_response_code(401);
					writeLog('Access denied: auth required.');
					throw new BW_Error('Page is locked/pending, only admin, author and those have the privilege could access this resource, please auth first.');
				}
				break;
			
			  case 'P': #Pending page
				if (
					$this->client['Group'] != '@Admin' &&
					$this->client['Username'] != $this->data['Author']
				) {
					http_response_code(403);
					writeLog('Access denied: pending page.');
					throw new BW_Error('Page is locked/pending, only admin and the author have the privilege to access this resource, please auth first.');
				}
				break;
			
			  case 'O': #OK
			  case 'C': #Construction
			  case 'D': #Deprecated
			  case 'S':
				break;
			
			  default:
				http_response_code(500);
				writeLog('Invalid status code.',true);
				throw new BW_Error( DEBUGMODE ?
					'Bearweb framework server error: Status code not supported.' :
					'Bearweb framework server-side error'
				);
			}
			writeLog('Page status processed.');
			
			//Send page misc headers
			header('Content-Type: '.$this->data['MIME']);
			if ($this->data['CreateTime'] == '1000-01-01 00:00:00') {
				header('Last-Modified: '.date('D, j M Y G:i:s').' GMT');
				header('Etag: '.md5(rand()));
			}
			else {
				header('Last-Modified: '.date('D, j M Y G:i:s',strtotime($this->data['LastModify'])).' GMT');
				header('Etag: '.$this->data['LastModify']);
			}
			writeLog('Page processed.');
		}
		
		
		//Debug using
		function __debuginfo() {
			$return = array(
				'URL' => $this->URL,
				'site' => array(
					'Closede' => $this->site['Closed']
				),
				'client' => $this->client,
				'data' => $this->data
			);
			$return['data']['Data'] = '==STRING==';
			$return['data']['Binary'] = '==BINARY==';
			return $return;
		}

	}
?>
