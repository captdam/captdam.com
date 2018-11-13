<div class="main_content_title">
	<h1><?php echo $BW->data['Title']; ?></h1>
	<p>By: <?php echo $BW->data['Author']; ?></p>
</div>
<div>
	<p>简介：<?php echo $BW->data['Description']; ?></p>
	<p class="content_keywords"><?php echo $BW->data['Keywords']; ?></p>
	<p>修改：<?php echo $BW->data['LastModify']; ?></p>
	<p>发布：<?php echo $BW->data['CreateTime']; ?></p>
	<p>版本：Version V.<?php echo $BW->data['Version']; ?></p>
</div>
<?php
	writeLog('Print content.');
	echo $BW->data['Data'];
?>