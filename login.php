<?php
/**
 * 登陆
 */
	require 'inc.php';
	session_start();
	$error = array();
	if($_POST['do']=='登陆'){

		if(empty($_POST['id'])){
			$error[] = "请输入ID";
		}
		if(empty($_POST['password'])){
			$error[] = "请输入密码";
		}
		if(empty($error)){
			$redis = new Redis();
	        $redis->connect($config['redis']['host'], $config['redis']['port']);
	        $user_id = intval($_POST['id']);
	        $userInfo = $redis->hgetall('user|'.$user_id);
	        if(!empty($userInfo)){

	        	if($userInfo['password']!=md5(trim($_POST['password']))){
	        		echo "<script>alert('账号有误');</script>";
	        	}else{
	        		$session_id = session_id();
	        		setcookie("sid",$session_id,time()+3600*24,"/");
	        		$redis->hset('user|login',$session_id,$user_id);
	        		echo "<script>alert('登陆成功');window.location.href='index.html'</script>";
	        	}
	        	
	        }else{
	        	echo "<script>alert('请先注册');</script>";
	        }
		}
		
	}

?>

<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>即时通讯-登陆</title>
	<style>
	ul li {
		padding: 10px;
	}
	input{width: 100;height: 18px;line-height: 18px;}
	</style>
</head>
<body>
	<h2>即时通讯-登陆</h2>

	<form id='form1' action="login.php" method='post'>
		<input type="hidden" name='do' value='登陆'>
		<ul>
			<li>ID：<input type="text" name='id'></li>
			<li>密码：<input type="password" name='password'></li>
			
			<li><button>登陆</button> | <a href="reg.php">注册</a></li>
		</ul>
	</form>
	 <?=implode('<br>', $error)?> 
</body>
</html>