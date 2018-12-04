<?php
	header('Content-Type: text/html');
	header('Cache-Control: no-cache, no-store, must-revalidate');
?>
<!DOCTYPE html>
<html
	data-pagestatus="<?php echo $BW->data['Status']; ?>"
	data-httpstatus="<?php echo http_response_code(); ?>"
>
	<head>
		<title><?php echo $BW->data['Title']; ?> - Captdam's blog</title>
		<meta name="keywords" content="<?php echo $BW->data['Keywords']; ?>" />
		<meta name="description" content="<?php echo $BW->data['Description']; ?>" />
		<meta name="author" content="<?php echo $BW->data['Author']; ?>" />
		<meta name="robots" content="<?php echo $BW->data['Status'] == 'S' ? 'noindex' : 'index'; ?>" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta charset="utf-8" />
		<link href="/web/favorite.png" rel="icon" type="image/png" />
		<link href="http://beardle.com/web/style.css" rel="stylesheet" type="text/css" />
		<script src="http://beardle.com/web/ajax.js"></script>
		<script src="http://beardle.com/web/md5.js"></script>
		<script src="http://beardle.com/web/util.js"></script>
		<script src="http://beardle.com/web/user.js"></script>
		<script src="http://beardle.com/web/ini.js"></script>
	</head>
	<body>
		<header>
			<h1 id="header_logo">Captdam's blog</h1>
			<span id="phone_menu_button">≡</span>
			<div id="search_container">
				<input id="search" />
			</div>
			<nav id="header_nav">
				<a href="/">主页</a>
				<a href="/about">关于</a>
				<a href="/blog">博客</a>
				<a href="/note">随笔</a>
				<a href="/resource">资源</a>
				<a href="/user" id="header_nav_user">登录</a>
			</nav>
		</header>
		<img id="banner" alt="Banner image" src="/<?php echo isset($BW->data['JSON']['poster']) ? $BW->data['JSON']['poster'] : 'web/banner.jpg' ?>" />
		<div id="side">
			<img src="http://beardle.com/web/top.png" alt="Top of page" title="To page top" />
		</div>
		<main>
			<div id="main_title">
				<h1><?php echo $BW->data['Title']; ?></h1>
			</div>
			<div id="main_content">
				<?php $BW->useTemplate(); ?>
			</div>
		</main>
		<footer>
			<div class="pltr">
				<img src="/web/logo.png" />
				<div>
					<p>Captdam e-mail: <a href="mailto:captdam@beardle.com">captdam@beardle.com</a></p>
					<p>© <?php
						if (substr($BW->data['Copyright'],0,10) == 'Reference=')
							echo substr($BW->data['Copyright'],10),' Uploaded by: ',
								'<span class="bearweb_author">',$BW->data['Author'],'</span> ';
						else {
							echo '<span class="bearweb_author">',$BW->data['Author'],'</span> ★';
							if ($BW->data['Copyright'] != 'All rights reserved')
								echo 'This work is licensed under ';
							echo $BW->data['Copyright'];
						}
					?></p>
				</div>
			</div>
		</footer>
		<style>
			@media (min-width: 768px) {
				header {
					background-image: url('/web/logo.png');
				}
			}
			@media (min-width: 1024px) {
				header {
					background-position: left calc(50% - 330px) center;
				}
			}
		</style>
	</body>
	
</html>
