<div class="main_content_title">
	<h1>欢迎来到Captdam的空间</h1>
	<p>Welcome to Captdam's zone</p>
</div>
<a href="/about" class="menu" style="background-image: url('http://beardle.com/web/home_about_long2.jpg');">
	<img src="http://beardle.com/web/home_about.png" alt="pic" />
	<div>
		<h2>关于</h2>
		<p>作为作者与开发者的碎碎念</p>
	</div>
</a>
<a href="/blog" class="menu" style="background-image: url('http://beardle.com/web/home_blog_long.jpg');">
	<img src="http://beardle.com/web/home_blog.png" alt="pic" />
	<div>
		<h2>博客</h2>
		<p>我发表的文章</p>
	</div>
</a>
<a href="/note" class="menu" style="background-image: url('http://beardle.com/web/home_note_long.jpg');">
	<img src="http://beardle.com/web/home_note.png" alt="pic" />
	<div>
		<h2>随笔</h2>
		<p>平时写的笔记</p>
	</div>
</a>
<a href="/resource" class="menu" style="background-image: url('http://beardle.com/web/home_resource_long.jpg');">
	<img src="http://beardle.com/web/home_resource.png" alt="pic" />
	<div>
		<h2>资源</h2>
		<p>分享一些微小的贡献</p>
	</div>
</a>
<div>
	<p>欢迎来到我的个人网站。</p>
	<p>我是Captdam，一个大学工科狗，一个DIY爱好者。</p>
	<p>这里是我的Blog网站。这个网站是我用于发布project的地方，重点主要在电子硬件，软件一类的主题上。简单的来说，这个网站就是一个极客blog。</p>
	<p>这个网站使用Bearweb搭建，是一个prototype类型的网站，网站系统处于<del>稳如poi的</del>在线实验中，<del>随时可能假摔</del>。有一天发现自己可以造轮子，于是就决定要自己一个人造跑车系列。 _(:3 _| <)__</p>
</div>

<div class="main_content_title">
	<h1>最新更新</h1>
	<p>Recently updates</p>
</div>
<?php
	writeLog('Get page list for all category in the set, offset: 0');
	$recent = $this->database->getRecentPagesAllCate(10);
	foreach($recent as $page) {
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
?>
