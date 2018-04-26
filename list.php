<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>即时通讯</title>
	<link rel="stylesheet" href="js/layui-v2.2.6/layui/css/layui.css">
	<link rel="stylesheet" href="css/common.css">
</head>
<body>
	<div class='list-title'>
	<fieldset class="layui-elem-field">
	  <div class="layui-field-box">
	    免费开源，真的，真的，真的，真的，真的，真的，真的，真的。
	  </div>
	</fieldset>
	</div>
	<div class="layui-tab layui-tab-brief list-content" >
	  <ul class="layui-tab-title">
	    <li class="layui-this"><i class="layui-icon icon-size">&#xe612;</i></li>
	    <li><i class="layui-icon icon-size">&#xe613;</i></li>
	    <!-- <li><i class="layui-icon icon-size">&#xe63a;</i></li> -->
	  </ul>
	  <div class="layui-tab-content list-box">
	    <div class="layui-tab-item layui-show" >

			<div class="layui-collapse chat-friend">

			</div>
	    </div>
	    <div class="layui-tab-item">
	    	<div class="layui-collapse chat-group">

			</div>
	    </div>
	   
	  </div>
	</div>    

	<div class='list-footer'>
		 
		<div class="layui-btn-group">

		  <button class="layui-btn layui-btn-primary" >
		    <i class="layui-icon icon-size">&#xe645;</i>
		  </button>
		  <button class="layui-btn layui-btn-primary add-friend">
		    友
		  </button>
		  <button class="layui-btn layui-btn-primary add-group">
		    群
		  </button>
		  <button class="layui-btn layui-btn-primary create-group">
		    <i class="layui-icon icon-size">&#xe608;</i>
		  </button>
		  
		</div>
	</div>
	 
	<script src="js/layui-v2.2.6/layui/layui.js"></script>
	<script src="js/jquery.min.js"></script>
	<script>


	//注意：折叠面板 依赖 element 模块，否则无法进行功能性操作
	function update_list () {
		layui.use('element', function(){
			var element = layui.element;
			element.init();
		});

	}
	
	/*更新未读数量*/
	function update_unread_num(user_id,num){
		if(!num || num==0 || num=='0'){
			$('.tip_'+user_id+'_num').hide();
		}else{
			$('.tip_'+user_id+'_num').html(num).show();
		}
	    
	}

	/*更新未读数量*/
	function update_group_unread_num(group_id,num){
		if(!num || num==0 || num=='0'){
			$('.tip_group_'+group_id+'_num').hide();
		}else{
			$('.tip_group_'+group_id+'_num').html(num).show();
		}
	    
	}


	</script> 
</body>


</html>