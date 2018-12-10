<?php //Login page
	if (!isset($BW->client['Nickname'])) { //Only register user has nickname
		writeLog('Print login page (saved in db).');
		echo $this->data['Data'];
		return;
	}
	writeLog('Print user console page.');
?>

<div class="main_content_title">
	<h1>用户面板</h1>
	<p>Hello，<?php echo $BW->client['Username']; ?></p>
</div>
<div class="bearform">
	<h2>基本资料</h2>
	<div class="pltr">
		<img id="modifyAvatarPreview" src="/user/photo?username=<?php echo $BW->client['Username']; ?>" style="width: 200px; height: 200px; background-color: #000000;" />
		<div>
			<label>Username</label>
			<input value="<?php echo $BW->client['Username']; ?>" disabled />
			<label>Nickname</label>
			<input id="modifyNickname" value="<?php echo $BW->client['Nickname']; ?>" />
			<label>分组</label>
			<input value="<?php echo $BW->client['Group']; ?>" disabled />
			<label>Email</label>
			<input id="modifyEmail" value="<?php echo ($BW->client['Email'] ? $BW->client['Email'] : '未填写'); ?>" />
			<label>头像</label>
			<input id="modifyAvatar" type="file" accept="image/*" />
		</div>
	</div>
	<button onclick="user.modifyProfile()">修改资料</button>
</div>
<div class="bearform">
	<h2>密码修改</h2>
	<label>旧密码</label>
	<input id="modifyPasswordOld" type="password" />
	<ul class="info">
		<li>以前的密码。</li>
	</ul>
	<label>新密码</label>
	<input id="modifyPasswordNew" type="password" />
	<ul class="info">
		<li>英文与数字组合。</li>
		<li>6-16个字符。</li>
		<li>将来可修改。</li>
		<li>请牢记本密码作为你的登录凭证。</li>
	</ul>
	<label>重复密码</label>
	<input id="modifyPasswordRepeat" type="password" />
	<ul class="info">
		<li>重复一遍新密码。</li>
	</ul>
	<button onclick="user.modifyAccount()">修改密码</button>
</div>
<div>
	*****
</div>
<div class="bearform">
	<button onclick="user.logout()">退出账户</button>
</div>
<script>
	document.getElementById('modifyAvatar').addEventListener('change',function(){
		var avatar = this.files[0];
		var reader = new FileReader();
		reader.readAsDataURL(avatar);
		reader.onload = function(){
			var preview = document.getElementById('modifyAvatarPreview');
			preview.dataset.data64 = reader.result.replace(/^data\:.*?\/.*?\;base64\,/,'').replace('-','+').replace('_','/');
			preview.src = window.URL.createObjectURL(avatar);
		};
	});
</script>

<?php //Admin page
	if ($BW->client['Group'] != '@Editor' && $BW->client['Group'] != '@Admin')
		return;
?>
<div class="main_content_title">
	<h1>管理员界面</h1>
	<p>Admin panel</p>
</div>
<div id="admin"><ul>
	<li><a href="/user/ide">Beardle Publisher's IDE (Basic)</a></li>
	<li><del href="/admin/url">查看管理URL列表/修改单个页面</del></li>
	<li><del href="/admin/upload">批量上传文件（图片）</del></li>
</ul></div>
<?php
	if ($BW->client['Group'] != '@Admin')
		return;
?>
<div>
	<h2>网站后台</h2>
	<b>Disk space: </b>
	<?php echo disk_free_space('./')/1048576,' / ',disk_total_space('./')/1048576,'MB' ?><br />
</div>