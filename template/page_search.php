<div class="main_content_title">
	<h1>搜索结果</h1>
	<p>Searching results</p>
</div>
<?php
	$pageSize = 20;
	$cp = getInputPage();
	
	$pass = true;
	if (!isset($_GET['search']))
		$pass = false;
	$search = trim($_GET['search']);
	if (strlen($search) > 32 || strlen($search) == 0)
		$pass = false;
	if (!$pass) {
		http_response_code(400);
		define('TEMPLATE_NOTEERROR',1);
		throw new BW_Error('Bad searching keyword (maxium 32 characters).');
	}
	
	if (!isset($_GET['search'])) {
		echo '<div>错误！请输入关键字以进行搜索。</div>';
		return;
	}
	$search = trim($_GET['search']);
	
	if (strlen($search) > 32) {
		echo '<div>错误！输入字符串过长，（最大长度32字符）。</div>';
		return;
	}
	if (strlen($search) == 0) {
		echo '<div>错误！请输入关键字以进行搜索。</div>';
		return;
	}
	
	writeLog('Searching page with keyword '.$search.', offset: '.$cp);
	$pages = $this->database->searchKeyword($search,$pageSize,$cp);
	foreach($pages as $page) {
		echo '<a href="'.$page['URL'].'" class="contentlist" data-bgimage="/',
			($page['Poster'] ? $page['Poster'] : 'NONE'),
			'"><div>',
			'<h2>',$page['Title'],'</h2>',
			'<p class="content_description">',$page['Description'],'</p>',
			'<p class="content_keywords">',$page['Keywords'],'</p>',
			'<p class="content_author">',
				$page['AuthorNickname'],
				'<span class="info"> @',$page['Author'],'</span>',
			'</p>',
			'<p class="content_lastmodify">',$page['LastModify'],'</p>',
			'</div></a>';
	}
	
	$currentSize = sizeof($pages);
	writeLog('Result: '.$currentSize);
	
	if ($currentSize == 0) {
		echo '<div class="pltr">',
			'<img src="http://beardle.com/web/heihua.jpg" alt="装傻" />',
			'<div>',
			'<h2>找不到结果</h2>',
			'<del>结果，不存在的，这辈子都不可能有的</del>',
			'<p>当前分类，当前分页，找不到任何结果。</p>',
			'<p>目前还没有足够达到你输入的页码那么多的结果，你可以尝试点击“首页”。</p>',
			'</div>',
			'</div>';
	}
	
	echo '<div class="resultlabels">';
	echo '<a href="/',$BW->URL,'?search=',$search,'">首页</a>';
	if ($cp > 1) {
		echo '<a href="/',$BW->URL,'?page=',$cp-1,'&search=',$search,'">上一页</a>';
	}
	if ($currentSize == $pageSize) {
		echo '<a href="/',$BW->URL,'?page=',$cp+1,'&search=',$search,'">下一页</a>';
	}
	echo '</div>';
?>
