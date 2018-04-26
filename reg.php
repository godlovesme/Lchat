<?php
/**
 * 注册
 */
	require 'inc.php';
	session_start();
	$error = array();
	if($_POST['do']=='注册'){

		if(empty($_POST['name'])){
			$error[] = "请输入名字";
		}
		if(empty($_POST['password'])){
			$error[] = "请输入密码";
		}
		if(empty($error)){
			$redis = new Redis();
	        $redis->connect($config['redis']['host'], $config['redis']['port']);

	        $redis->hincrby('config','user_id',1);
	        $user_id = $redis->hget('config','user_id');

	        $redis->hmset('user|'.$user_id,array(
	        	'id'=>$user_id,
	        	'name'=>$_POST['name'],
	        	'password'=>md5(trim($_POST['password'])),
	        	'birthday'=>$_POST['birthday'],
	        	'email'=>$_POST['email'],
	        	'phone'=>$_POST['phone'],
	        	'face'=>$_POST['face'],
	        ));
	        $userInfo = $redis->hgetall('user|'.$user_id);
	        if(!empty($userInfo)){
	        	echo "<script>alert('注册成功 登陆ID为：".$user_id."');window.location.href='?time='+Math.random()</script>";
	        }else{
	        	echo "<script>alert('注册失败');</script>";
	        }
		}
		
	}

?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>即时通讯-注册</title>
	<style>
	ul li {
		padding: 10px;
	}
	input{width: 100;height: 18px;line-height: 18px;}
	</style>
</head>
<body>
	<h2>即时通讯-注册</h2>

	<form id='form1' action="reg.php" method='post'>
		<input type="hidden" name='do' value='注册'>
		<ul>
			<li>名字：<input type="text" name='name'></li>
			<li>密码：<input type="password" name='password'></li>
			<li>生日：<input type="date" name='birthday'></li>
			<li>邮箱：<input type="text" name='email'></li>
			<li>手机：<input type="text" name='phone'></li>
			<li>头像：<input name="face" type="radio" value='01'><img src="image/01.png" width=50 height=50 alt="">
			<input name="face" type="radio" value='02'><img src="image/02.png" width=50 height=50 alt="">
			<input name="face" type="radio" value='03'><img src="image/03.png" width=50 height=50 alt=""></li>
			<li><button>注册</button></li>
		</ul>
	</form>
	 <?=implode('<br>', $error)?> 
</body>
</html>

