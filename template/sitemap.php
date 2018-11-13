<?php
	//Get header
	$site = $_SERVER['SERVER_NAME'];
	$cp = getInputPage();
	echo '<?xml version="1.0" encoding="UTF-8"?>';
	echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">';
	
	//Fetch DB
	writeLog('Fetch sitemap, count 1000, page: '.$cp);
	$sitemap = $BW->database->getSitemap(1000,$cp);
	foreach($sitemap as $x) {
		echo '<url>';
		echo '<loc>http://',$site,'/',$x['URL'],'</loc>';
		if ($x['LastModify'] != '1000-01-01 00:00:00') echo '<lastmod>',str_replace(' ','T',$x['LastModify']),'+00:00</lastmod>';
		echo '</url>';
	}
	
	echo '</urlset>';
?>
