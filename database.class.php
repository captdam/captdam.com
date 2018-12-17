<?php
	//Bearweb database interface
	/* This includes all database operation functions */
	interface BearwebDatabaseInterface {
		//Framework core
		public function getSiteConfig();		#Get web site config saved in db
		public function getUserByUsername($username);	#Get all info of a user from db
		public function userActive($username);		#Refresh user LastActiveTime in db
		public function logRequest($url,$client,$tid);	#Log current request
		public function getPageByURL($url);		#Get all info of a page from db
		public function getSitemap($num,$page);		#Get XML sitemap
		public function logRequestEnd($s,$t,$pid,$sid);	#Log current request result
		
		//Template
		public function getRecentPagesAllCate($num);	#Get recent updates for all category
		public function getPagesByCate($ca,$num,$page);	#Get recent updates for a category
		public function countPagesAllCate();		#Count pages from all category
		public function countPagesByCategory($cate);	#Count pages for a category
		public function searchKeyword($key,$num,$page);	#Search contents contains the keyword
		public function newUser($un,$nn,$g,$pw,$ip,$p);	#Register a user
		public function updateUser($un,$pw,$nn,$m,$p);	#Update user info
		public function getWorkByUsername($username);	#Get work list of an user
		public function getSubwork($mainWork);		#Get sub work (add-on) of a work
		public function createPage($url,$data);		#Create a page
		public function updatePage($url,$data);		#Update a page
		public function updatePageIDE($url,$data);	#Update a page and its add-on
		public function deletePage($url);		#Delete a page by URL
		
		//Object storage server
		public function updateObjectStorage($token);	#Update Object stoarge server token
	}
	
	
	//Base database util
	class Database {
		
		private $db; #Database resource
		
		//Constructor function, connect to database
		function __construct($dbname,$dbhost,$dbuser,$dbpass) {
			try {
				$this->db = new PDO(
					'mysql:dbname='.$dbname.';host='.$dbhost.';charset=UTF8',
					$dbuser,
					$dbpass,
					array(
						PDO::ATTR_PERSISTENT			=> false,
						PDO::MYSQL_ATTR_USE_BUFFERED_QUERY	=> false
					)
				);
				$this->db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
			} catch(PDOException $e) {
				throw new BW_Error(' - Cannot connect to database.');
			}
		}
		
		//Execute procedure
		protected function call($procedure,$para,$return=false) {
			try {
				$current = $this->db->prepare('CALL '.$procedure);
				foreach($para as $key=>$value)
					$current->bindParam(':'.$key,$value[0],$value[1]);
				$current->execute();
				$data = $return ? $current->fetchAll(PDO::FETCH_ASSOC) : null;
				$current->closeCursor();
				return $data;
			} catch(PDOException $e) {
				throw new BW_Error(' - Fail to execute procedure '.$procedure.'. '.$e->getMessage().'.');
			}
		}
		
		//Execute query
		public function query($query,$para,$return=false) {
			try {
				$current = $this->db->prepare('CALL '.$procedure);
				foreach($para as $key=>$value)
					$current->bindParam(':'.$key,$value[0],$value[1]);
				$current->execute();
				$data = $return ? $current->fetchAll(PDO::FETCH_ASSOC) : null;
				$current->closeCursor();
				return $data;
			} catch(PDOException $e) {
				throw new BW_Error(' - Fail to execute query "'.$query.'". '.$e->getMessage().'.');
			}
		}
		
		//Transcation
		protected function begin() {
			try {
				$this->db->beginTransaction();
			} catch(PDOException $e) {
				throw new BW_Error(' - Fail to begin transaction '.$e->getMessage().'.');
			}
		}
		protected function commit() {
			try {
				$this->db->commit();
			} catch(PDOException $e) {
				throw new BW_Error(' - Fail to commit transaction '.$e->getMessage().'.');
			}
		}
		protected function rollback() {
			try {
				$this->db->rollback();
			} catch(PDOException $e) {
				throw new BW_Error(' - Fail to rollback transaction '.$e->getMessage().'.');
			}
		}
		
	}
	
	
	//Bearweb database util
	class BearwebDatabase extends Database implements BearwebDatabaseInterface{
		
		//Framework core
		
		public function getSiteConfig() {
			try {
				$site = $this->call(
					'siteConfig',
					array(),
					true
				);
			} catch(BW_Error $e) {
				throw new BW_Error(__METHOD__.$e->getMessage());
			}
			$config = array();
			foreach($site as $x) {
				$config[$x['Key']] = $x['Value'];
			}
			return $config;
		}
		
		public function getUserByUsername($username) {
			try {
				$user = $this->call(
					'userSearch',
					array(
						'username' => $username
					),
					true
				);
			} catch(BW_Error $e) {
				throw new BW_Error(__METHOD__.$e->getMessage());
			}
			
			if (!$user) #No such user
				return null;
			
			$user = $user[0];
			$user['Data'] = json_decode($user['Data'],true);
			return $user;
		}
		
		public function userActive($username) {
			try {
				$this->call(
					'userActive',
					array(
						'username' => $username
					)
				);
			} catch(BW_Error $e) {
				throw new BW_Error(__METHOD__.$e->getMessage());
			}
		}
		
		public function logRequest($url,$client,$tid) {
			try {
				$this->call(
					'logRequest',
					array(
						'url'		=> $url,
						'username'	=> $client['Username'],
						'userip'	=> $client['IP'],
						'phpid'		=> $tid,
						'sessionid'	=> $client['SID']
					)
				);
			} catch(BW_Error $e) {
				throw new BW_Error(__METHOD__.$e->getMessage());
			}
		}
		
		public function getPageByURL($url) {
			try {
				$page = $this->call(
					'pageGetFull',
					array(
						'url' => $url
					),
					true
				);
			} catch(BW_Error $e) {
				throw new BW_Error(__METHOD__.$e->getMessage());
			}
			
			if (!$page) #No such page (404)
				return null;
			
			$page = $page[0];
			$page['JSON'] = json_decode($page['JSON'],true);
			return $page;
		}
		
		public function getSitemap($num,$page) {
			try {
				return $this->call(
					'sitemap',
					array(
						'count'		=> $num,
						'pageoffset'	=> $num * $page - $num
					),
					true
				);
			} catch(BW_Error $e) {
				throw new BW_Error(__METHOD__.$e->getMessage());
			}
		}
		
		public function logRequestEnd($s,$t,$pid,$sid) {
			try {
				$this->call(
					'logRequestEnd',
					array(
						'status'	=> $s,
						'time'		=> $t,
						'phpid'		=> $pid,
						'sessionid'	=> $sid
					)
				);
			} catch(BW_Error $e) {
				throw new BW_Error(__METHOD__.$e->getMessage());
			}
		}
		
		//Template
		
		public function getRecentPagesAllCate($num) {
			try {
				return $this->call(
					'pagesRecent',
					array(
						'count'		=> $num,
						'pageoffset'	=> 0,
						'category'	=> CATEGORYSET,
						'special'	=> false
					),
					true
				);
			} catch(Exception $e) {
				throw new BW_Error(__METHOD__.$e->getMessage());
			}
		}
		
		public function getPagesByCate($ca,$num,$page) {
			try {
				return $this->call(
					'pagesRecent',
					array(
						'count'		=> $num,
						'pageoffset'	=> $num * $page - $num,
						'category'	=> $ca,
						'special'	=> true
					),
					true
				);
			} catch(Exception $e) {
				throw new BW_Error(__METHOD__.$e->getMessage());
			}
		}
		
		public function countPagesAllCate() {
			try {
				return $this->call(
					'pagesCount',
					array(
						'category'	=> CATEGORYSET,
						'special'	=> false,
						'ap'		=> false
					),
					true
				)[0]['X'];
			} catch(Exception $e) {
				throw new BW_Error(__METHOD__.$e->getMessage());
			}
		}
		
		public function countPagesByCategory($cate) {
			try {
				return $this->call(
					'pagesCount',
					array(
						'category'	=> $cate,
						'special'	=> true,
						'ap'		=> false
					),
					true
				)[0]['X'];
			} catch(Exception $e) {
				throw new BW_Error(__METHOD__.$e->getMessage());
			}
		}
		
		public function searchKeyword($key,$num,$page) {
			try {
				return $this->call(
					'pagesSearch',
					array(
						'count'		=> $num,
						'pageoffset'	=> $num * $page - $num,
						'keyword'	=> $key,
						'category'	=> CATEGORYSET
					),
					true
				);
			} catch(Exception $e) {
				throw new BW_Error(__METHOD__.$e->getMessage());
			}
		}
		
		public function newUser($un,$nn,$g,$pw,$ip,$p) {
			try {
				$this->call(
					'userRegister',
					array(
						'username'	=> $un,
						'nickname'	=> $nn,
						'group'		=> $g,
						'password'	=> $pw,
						'registerip'	=> $ip,
						'photo'		=> $p
					)
				);
			} catch(Exception $e) {
				if (strpos($e->getMessage(),'1062 Duplicate'))
					return false;
				throw new BW_Error(__METHOD__.$e->getMessage());
			}
			return true;
		}
		
		public function updateUser($un,$pw,$nn,$m,$p) {
			try {
				$this->call(
					'userModify',
					array(
						'username'	=> $un,
						'nickname'	=> $nn,
						'password'	=> $pw,
						'email'		=> $m,
						'photo'		=> $p
					)
				);
			} catch(Exception $e) {
				throw new BW_Error(__METHOD__.$e->getMessage());
			}
		}
		
		public function getWorkByUsername($username) {
			try {
				return $this->call(
					'userPages',
					array(
						'username'	=> $username,
						'category'	=> CATEGORYSET
					),
					true
				);
			} catch(Exception $e) {
				throw new BW_Error(__METHOD__.$e->getMessage());
			}
		}
		
		public function getSubwork($mainWork) {
			try {
				return $this->call(
					'pageSubwork',
					array(
						'work'		=> $mainWork.'/%'
					),
					true
				);
			} catch(Exception $e) {
				throw new BW_Error(__METHOD__.$e->getMessage());
			}
		}
		
		public function createPage($url,$data) {
			try {
				$this->call(
					'pageCreate',
					array(
						'url'		=> $url,
						'title'		=> $data['Title'],
						'author'	=> $data['Author'],
						'category'	=> $data['Category'],
						'mime'		=> $data['MIME'],
						'status'	=> $data['Status'],
					)
				);
			} catch(Exception $e) {
				throw new BW_Error(__METHOD__.$e->getMessage());
			}
		}
		
		public function updatePage($url,$data) {
			try {
				$this->call(
					'pageModify',
					array(
						'url'		=> $url,
						'mime'		=> $data['MIME'],
						'title'		=> $data['Title'],
						'keywords'	=> $data['Keywords'],
						'description'	=> $data['Description'],
						'category'	=> $data['Category'],
						'author'	=> $data['Author'],
						'templatemain'	=> $data['TemplateMain'],
						'templatesub'	=> $data['TemplateSub'],
						'data'		=> $data['Data'],
						'binary'	=> $data['Binary'],
						'json'		=> $data['JSON'],
						'copyright'	=> $data['Copyright'],
						'status'	=> $data['Status'],
					)
				);
			} catch(Exception $e) {
				throw new BW_Error(__METHOD__.$e->getMessage());
			}
		}
		
		public function updatePageIDE($url,$data) {
			$this->begin();
			try {
				$this->call(
					'pageModify',
					array(
						'url'		=> $url,
						'mime'		=> $data['MIME'],
						'title'		=> $data['Title'],
						'keywords'	=> $data['Keywords'],
						'description'	=> $data['Description'],
						'category'	=> $data['Category'],
						'author'	=> $data['Author'],
						'templatemain'	=> $data['TemplateMain'],
						'templatesub'	=> $data['TemplateSub'],
						'data'		=> $data['Data'],
						'binary'	=> $data['Binary'],
						'json'		=> $data['JSON'],
						'copyright'	=> $data['Copyright'],
						'status'	=> $data['Status'],
					)
				);
				$this->call(
					'pageModifyIDE',
					array(
						'url'		=> $url.'/%',
						'keywords'	=> $data['Keywords'],
						'description'	=> $data['Description'],
						'copyright'	=> $data['Copyright'],
						'status'	=> $data['Status'],
					)
				);
			} catch(Exception $e) {
				$this->rollback();
				throw new BW_Error(__METHOD__.$e->getMessage());
			}
			$this->commit();
		}
		
		public function deletePage($url) {
			try {
				$this->call(
					'pageDelete',
					array(
						'url'		=> $url
					)
				);
			} catch(Exception $e) {
				throw new BW_Error(__METHOD__.$e->getMessage());
			}
		}
		
		//Object storage server
		
		public function updateObjectStorage($token) {
			try {
				$this->call(
					'siteOSUpdate',
					array(
						'token'		=> $token
					)
				);
			} catch(Exception $e) {
				throw new BW_Error(__METHOD__.$e->getMessage());
			}
		}
		
		//Microcode
		
		function __construct() {
			try {
				parent::__construct(DB_NAME,DB_HOST,DB_USERNAME,DB_PASSWORD);
			} catch(BW_Error $e) {
				throw new BW_Error(__METHOD__.$e->getMessage());
			}
			writeLog(__METHOD__.' - Database inferface created.');
		}
		
		protected $lookup = array( #Database procedure lookup table
			'logRequest'	=> ['url','username','userip','phpid','sessionid'],
			'logRequestEnd'	=> ['status','time','phpid','sessionid'],
			'pageCreate'	=> ['url','title','author','category','mime','status'],
			'pageDelete'	=> ['url'],
			'pageGetFull'	=> ['url'],
			'pageModify'	=> ['url','mime','title','keywords','description','category','author','templatemain','templatesub','data','binary','json','copyright','status'],
			'pageModifyIDE'	=> ['url','keywords','description','copyright','status'],
			'pagesCount'	=> ['category','special','ap'],
			'pagesRecent'	=> ['count','pageoffset','category','special'],
			'pagesSearch'	=> ['count','pageoffset','keyword','category'],
			'pageSubwork'	=> ['work'],
			'siteConfig'	=> [],
			'sitemap'	=> ['count','pageoffset'],
			'siteOSUpdate'	=> ['token'],
			'userActive'	=> ['username'],
			'userModify'	=> ['username','nickname','group','password','email','data','photo'],
			'userPages'	=> ['username','category'],
			'userRegister'	=> ['username','nickname','group','password','registerip','photo'],
			'userSearch'	=> ['username','nickname']
		);
		
		protected function call($procedure,$param,$return=false) {
			writeLog(__METHOD__.' - Executing procedure: '.$procedure);
			
			if(!array_key_exists($procedure,$this->lookup))
				throw new BW_Error(__METHOD__.' - Procedure not supported.');
			
			$paramSet = array_map(function($x){
				return ':'.$x;
			},$this->lookup[$procedure]);
			$sendProce = $procedure.'('.implode(',',$paramSet).')';
			
			$sendParam = array_fill_keys($this->lookup[$procedure],[NULL,PDO::PARAM_NULL]);
			foreach ($param as $key => $value) #Bind param, ignor keys not indentified
				if (array_key_exists($key,$sendParam)) {
					switch (gettype($value)) {
					  case 'boolean':
						$type = PDO::PARAM_BOOL;
						break;
					  case 'integer':
						$type = PDO::PARAM_INT;
						break;
					  case 'double':
						$value = strval($value); #Convert to string
					  case 'string':
						$type = PDO::PARAM_STR;
						break;
					  case 'NULL':
						$type = PDO::PARAM_NULL;
						break;
					  case 'resource':
						$type = PDO::PARAM_LOB;
						break;
					  default:
						throw new BW_Error(__METHOD__.' - Param type not supported.');
					}
					$sendParam[$key] = [$value,$type];
				}
			
			try {
				$return = parent::call($sendProce,$sendParam,$return);
			} catch(BW_Error $e) {
				throw new BW_Error(__METHOD__.$e->getMessage());
			}
			
			writeLog(__METHOD__.' - Procedure executed.');
			return $return;
		}
	}
?>