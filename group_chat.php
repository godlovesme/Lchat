<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>即时通讯</title>
	<link rel="stylesheet" href="js/layui-v2.2.6/layui/css/layui.css">
	<link rel="stylesheet" href="css/common.css">
</head>
<body>
	
	<div class='chat'>
		<div class="layui-tab" style="margin:0;" lay-allowClose="true" lay-filter="chat-win">
			<ul class="layui-tab-title">
			</ul>
			<div class="layui-tab-content">
			</div>
		</div>
	</div>
	<div class='do-chat'>
		<div class='chat-icon'>
			<i class="layui-icon emotion">&#xe6af;</i>  
		</div>
		<textarea name="desc" placeholder="请输入内容" class="layui-textarea send_content" id="desc" ></textarea>

		<div class='chat-btn' >
		  <button type="reset" class="layui-btn layui-btn-primary">关闭</button>
		  <button class="layui-btn do-send" lay-submit lay-filter="formDemo">发送</button>
		</div>
		<input type="hidden" class="to_group_id" >
	</div>
	 
	<script src="js/layui-v2.2.6/layui/layui.js"></script>
	<script src="js/jquery.min.js"></script>
	<script type="text/javascript" src="js/qqFace/js/jquery.qqFace.js"></script>
	<script>


	//注意：折叠面板 依赖 element 模块，否则无法进行功能性操作
	layui.use(['element'], function(){
		var element = layui.element;
		element.on('tabDelete(chat-win)', function(data){
		  console.log(this); //当前Tab标题所在的原始DOM元素
		  console.log(data.index); //得到当前Tab的所在下标
		  console.log(data.elem); //得到当前的Tab大容器
		  console.log($('.chat .layui-tab-title li').size());

		  
		  if($('.chat .layui-tab-title li').size()==0){
		  	window.parent.chat_group_layer.close(window.parent.chat_group_layer.index); 
		  	window.parent.chat_group_iframe_win = null;
		  	window.parent.chat_group_body = null;
		  	window.parent.chat_group_ids = [];
		  }
		});

		element.on('tab(chat-win)', function(data){
		  console.log(this); //当前Tab标题所在的原始DOM元素
		  console.log(data.index); //得到当前Tab的所在下标
		  console.log(data.elem); //得到当前的Tab大容器
		  var id = $(this).attr('lay-id');
		  $('.to_group_id').val(id);
		});

	});

	function add_tab(id,name,content){
		layui.use('element', function(){
			var element = layui.element;
			element.tabAdd('chat-win', {
				title: name
				,content: content
				,id: id
			});  
			element.tabChange('chat-win', id); 
			$('.to_group_id').val(id);
		}.bind(id,name,content));
	}

	function change_tab(id){
		layui.use('element', function(){
			var element = layui.element;
			element.tabChange('chat-win', id); 
			$('.to_group_id').val(id);
		}.bind(id));
		
	}
	/*获取当前正在对话的用户*/
	function get_chat_group_id(){
		return $('.to_group_id').val();
	}
	/*显示对方聊天内容*/
	function add_group_chat_content_to(data){
		data.content = data.content.replace(/\n/g,"<br>");
		$('.layui-show').html(function(k,v){
			var _content = `<div class='chat-user-left'>
					<div class="layui-anim layui-anim-fadein user-img">
						<img  src="image/`+data.user_face+`.png" alt="" >
						<ul>
							<li class='user-title cut-text'>`+data.add_time+` `+data.user_name+`</li>
							<li class='user-content'>`+replace_em(data.content)+`</li>
						</ul>
			      	</div>
				</div>`;
			return v+_content;
		});
		$('.layui-show').animate({scrollTop:100000+'px'},500)
	}
	/*显示我方聊天内容*/
	function add_group_chat_content_from(data){
		data.content = data.content.replace(/\n/g,"<br>");
		$('.layui-show').html(function(k,v){
			var _content = `<div class='chat-user-right'>
					<div class="layui-anim layui-anim-fadein user-img">
						<img src="image/`+data.user_face+`.png" alt="" >
						<ul>
							<li class='user-title cut-text'>`+data.add_time+` `+data.user_name+`</li>
							<li class='user-content'>`+replace_em(data.content)+`</li>
						</ul>
			      	</div>
				</div>`;
			return v+_content;
		});
		$('.layui-show').animate({scrollTop:100000+'px'},500)
	}
	/*获取未读的内容*/
	function get_group_unread_content(list){
		for (var i = 0; i < list.length; i++) {
			var data = list[i];
			add_group_chat_content_to(data);
		};
	}

	$(function(){
		$('.do-send').click(function(){
			var to_group_id = $('.to_group_id').val();
			var send_content = $('.send_content').val();
			if(!send_content || send_content==undefined){
				window.parent.tip('请输入内容');
				return;
			}
			window.parent.chat_group(to_group_id,send_content);
			$('.send_content').val('');
		});
	});

	$('.emotion').qqFace({

		id : 'facebox', 

		assign:'desc', 

		path:'js/qqFace/arclist/'	//表情存放的路径

	});

	function replace_em(str){

		str = str.replace(/\</g,'&lt;');

		str = str.replace(/\>/g,'&gt;');

		str = str.replace(/\[em_([0-9]*)\]/g,'<img src="js/qqFace/arclist/$1.gif" border="0" />');

		return str;

	}

	</script> 
</body>


</html>