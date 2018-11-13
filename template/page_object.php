<div class="main_content_title">
	<h2>页面内容</h2>
</div>
<div id="pagecontent">
	<?php
		writeLog('Print object page frame.');
		if (substr($BW->data['MIME'],0,6) == 'image/') {
			echo '<div>';
			echo '<img src="/'.$BW->data['URL'].'?HD" class="clickimage" onclick="location.href=\'/'.$BW->data['URL'].'?HD\'"/>';
			echo '<br /><a href="/'.$BW->data['URL'].'?HD" style="text-align: center; display: block;">查看大图</a>';
			echo '</div>';
		}
		else {
			echo '<pre>'.$BW->data['Data'].'</pre>';
		}
	?>
</div>
<div class="main_content_title">
	<h2>页面信息</h2>
</div>
<div id="pageinfo">
	<h3><?php echo $BW->data['Title']; ?></h3>
	<p>URL: /<?php echo $BW->data['URL']; ?></p>
	<p>Author: <?php echo $BW->data['Author']; ?></p>
	<i>LastModify: <?php echo $BW->data['LastModify']; ?></i>
	<i>Version: <?php echo $BW->data['Version']; ?></i>
</div>
<div>
	<p>Keywords:<br /><?php echo $BW->data['Keywords'] ? $BW->data['Keywords'] : '<i>Null</i>'; ?></p>
	<p>Description:<br /><?php echo $BW->data['Description'] ? $BW->data['Description'] : '<i>Null</i>'; ?></p>
</div>
