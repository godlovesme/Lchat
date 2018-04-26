/**
*	核心文件
*/

var list_body = null;
var list_iframe_win = null;
var chat_body = null;
var chat_iframe_win = null;
var chat_layer = null;
var chat_user_ids = [];
var chat_group_body = null;
var chat_group_iframe_win = null;
var chat_group_layer = null;
var chat_group_ids = [];
var layer = layui.layer;
var element = layui.element;


layer.open({
	type: 2, 
	title: ['即时通讯', 'font-size:18px;'],
	content: ['list.php'],
	shade: 0,
	area: ['300px', '600px'],
	anim: 2,
	offset: 'rb',
	cancel: function(index, layero){ 
		layer.close(index)
	  	return false; 
	},
	success: function(layero, index){
	    var body = layer.getChildFrame('body', index);
	    var iframeWin = window[layero.find('iframe')[0]['name']]; 
	    list_body = body;
	    list_iframe_win = iframeWin;

        /*添加朋友*/
        $(body).on('click','.add-friend',function(){

            layer.prompt({
              formType: 0,
              value: '',
              title: '添加好友-请输入ID号',
              area: ['100px', '50px']
            }, function(value, index, elem){

            var userId = parseFloat(value);
            if(isNaN(userId)){
                layer.msg('请输入数字');
                return;
            }

            add_friend(userId);

            layer.close(index);

            });
        });

        /*创建群*/
        $(body).on('click','.create-group',function(){

            layer.prompt({
              formType: 0,
              value: '',
              title: '创建群-请输入群的名称',
              area: ['100px', '50px']
            }, function(value, index, elem){

            var group_name = $.trim(value);
            if(group_name=='' || group_name == undefined){
                layer.msg('请输入群的名称');
                return;
            }

            create_group(group_name);

            layer.close(index);

            });
        });
	    
        /*加入群*/
        $(body).on('click','.add-group',function(){

            layer.prompt({
              formType: 0,
              value: '',
              title: '添加群-请输入ID号',
              area: ['100px', '50px']
            }, function(value, index, elem){

            var group_id = parseFloat(value);
            if(isNaN(group_id)){
                layer.msg('请输入数字');
                return;
            }

            add_group(group_id);

            layer.close(index);

            });
        });

        /*好友聊天*/
	    $(body).on('click','.do-user-chat tr',function(){

			var user_id = $(this).attr('data-user-id');
			var user_name = $(this).attr('data-user-name');
            var chat_type = $(this).attr("data-type");
			var user_content = '';

			/*切换*/
			if($.inArray(user_id,chat_user_ids)!=-1){
				chat_iframe_win.change_tab(user_id);
                /*获取未读的内容*/
                get_chat_unread(user_id);
				return;
			}
			chat_user_ids.push(user_id);
			/*添加*/
			if(chat_iframe_win){
				chat_iframe_win.add_tab(user_id,user_name,user_content);
                /*获取未读的内容*/
                get_chat_unread(user_id);
				return;
			}

			/*打开*/
			chat_layer = layui.layer;
			chat_layer.open({
				type: 2,
				title: ['聊天', 'font-size:18px;'],
				content: ['user_chat.php'],
				shade: 0,
				area: ['800px', '600px'],
				success: function(layero, index){
					var body = chat_layer.getChildFrame('body', index);
    				var iframeWin = window[layero.find('iframe')[0]['name']]; 
    				chat_body = body;
    				chat_iframe_win = iframeWin;
    				iframeWin.add_tab(user_id,user_name,user_content);
                    /*获取未读的内容*/
                    get_chat_unread(user_id);

    				
				},
				cancel: function(index, layero){ 
					chat_layer.close(index);
					chat_iframe_win = null;
  					chat_body = null;
  					chat_user_ids = [];
				  	return false; 
				},

			});

		});

        /*群聊天*/
        $(body).on('click','.do-group-chat tr',function(){

            var group_id = $(this).attr('data-group-id');
            var group_name = $(this).attr('data-group-name');
            var user_content = '';

            /*切换*/
            if($.inArray(group_id,chat_group_ids)!=-1){
                chat_group_iframe_win.change_tab(group_id);
                /*获取未读的内容*/
                get_group_chat_unread(group_id);
                return;
            }
            chat_group_ids.push(group_id);
            /*添加*/
            if(chat_group_iframe_win){
                chat_group_iframe_win.add_tab(group_id,group_name,user_content);
                /*获取未读的内容*/
                get_group_chat_unread(group_id);
                return;
            }

            /*打开*/
            chat_group_layer = layui.layer;
            chat_group_layer.open({
                type: 2,
                title: ['聊天', 'font-size:18px;'],
                content: ['group_chat.php'],
                shade: 0,
                area: ['800px', '600px'],
                success: function(layero, index){
                    var body = chat_group_layer.getChildFrame('body', index);
                    var iframeWin = window[layero.find('iframe')[0]['name']]; 
                    chat_group_body = body;
                    chat_group_iframe_win = iframeWin;
                    iframeWin.add_tab(group_id,group_name,user_content);
                    /*获取未读的内容*/
                    get_group_chat_unread(group_id);

                },
                cancel: function(index, layero){ 
                    chat_group_layer.close(index);
                    chat_group_iframe_win = null;
                    chat_group_body = null;
                    chat_group_ids = [];
                    return false; 
                },

            });

        });


        ws_init('ws://192.168.1.158:9501');

	}
}); 


/*提示*/
function tip(msg){
    layer.msg(msg);
}

/*登陆显示*/
function update_user(data){
    layer.title(data)
} 

/*我的好友*/
function chat_get_friend(data){
	debug(data);
	for (var i = 0; i < data.length; i++) {
		
	
		var content = `<div class="layui-colla-item">
				    <h2 class="layui-colla-title">`+data[i]['title']+`(`+data[i]['total_num']+`)</h2>
				    <div class="layui-colla-content ">
						<table class="layui-table do-user-chat" lay-skin="row">
						  <tbody>`;

		data[i]['user_list'].forEach(function(v,k){
			content += `<tr class="user-chat-`+v['id']+`" data-type='user' data-user-id="`+v['id']+`" data-user-name="`+v['name']+`">
					  <td>
					  	<div class="layui-anim layui-anim-fadein user-img">
							<img src="image/`+v['face']+`.png" alt="" >
							<span class="layui-badge friend_tip_num tip_`+v['id']+`_num" ></span>
							<ul>
								<li class='cut-text'><h3>`+v['name']+`</h3></li>
								<li class='cut-text'>email:`+v['email']+`</li>
							</ul>
					  	</div>
					  </td>
					</tr>`;
		});

		    
		content +=  `</tbody>
						</table>
				    </div>
				  </div>`;
	};
	debug(content);
	$(list_body).find('.chat-friend').html(content);
	list_iframe_win.update_list()

}

/*群*/
function chat_get_group(data){
    debug(data);

    var content = `<table class="layui-table do-group-chat" lay-skin="row">
                          <tbody>`;
    for (var i = 0; i < data.length; i++) {
            var v = data[i];
            content += `<tr class="user-chat-`+v['id']+`" data-type='group' data-group-id="`+v['id']+`" data-group-name="`+v['name']+`">
                      <td>
                        <div class="layui-anim layui-anim-fadein user-img">
                            <img src="image/user.png" alt="" >
                            <span class="layui-badge friend_tip_num tip_group_`+v['id']+`_num" ></span>
                            <ul>
                                <li class='cut-text'><h3>`+v['name']+`</h3></li>
                                <li class='cut-text'>ID:`+v['id']+`</li>
                            </ul>
                        </div>
                      </td>
                    </tr>`;


    };
    content +=  `</tbody>
                </table>`;
    debug(content);
    $(list_body).find('.chat-group').html(content);
    list_iframe_win.update_list()

}




/*websocket*/
var is_conn = false;  
var websocket = null;  
var is_login = false;  
var usr = '';  
var i = 0;  
var re_time = null;  
var send_id = 0;  
var send_name = 'all';  
var me_id = 0;  

/*登陆*/
function login() {  
    if(!is_login){ 
        var to_data = {  
            'cmd':'login',  
            'data': {  
                'sid': get_cookie('sid')
            }
        };  
        var json = JSON.stringify(to_data);  
        websocket.send(json);  
    }
}
/*添加好友*/
function add_friend(user_id) {  
    
    var to_data = {  
        'cmd':'add_friend',  
        'data': {  
            'user_id': user_id
        }
    };  
    var json = JSON.stringify(to_data);  
    websocket.send(json);  

}
/*获取好友列表*/
function get_friend() {  
    
    var to_data = {  
        'cmd':'get_friend'
    };  
    var json = JSON.stringify(to_data);  
    websocket.send(json);  

}
/*发送聊天消息*/
function chat_friend(user_id,content){
	var to_data = {  
        'cmd':'chat_friend',
        'data':{
        	'user_id':user_id,
        	'content':content,
        }
    };  
    var json = JSON.stringify(to_data);  
    websocket.send(json); 
}

/*删除未读的消息*/
function del_chat_unread(user_id){
    var to_data = {  
        'cmd':'del_chat_unread',
        'data':{
            'user_id':user_id,
        }
    };  
    var json = JSON.stringify(to_data);  
    websocket.send(json); 
}

/*获取未读的消息*/
function get_chat_unread(user_id){
    var to_data = {  
        'cmd':'get_chat_unread',
        'data':{
            'user_id':user_id,
        }
    };  
    var json = JSON.stringify(to_data);  
    websocket.send(json); 
    del_chat_unread(user_id);
}

/*创建群*/
function create_group(group_name){
    var to_data = {  
        'cmd':'create_group',  
        'data': {  
            'group_name': group_name
        }
    };  
    var json = JSON.stringify(to_data);  
    websocket.send(json);  
}

/*获取我加入群*/
function get_group(){
    var to_data = {  
        'cmd':'get_group',  
    };  
    var json = JSON.stringify(to_data);  
    websocket.send(json);  
}

/*添加群*/
function add_group(group_id){  
    
    var to_data = {  
        'cmd':'add_group',  
        'data': {  
            'group_id': group_id
        }
    };  
    var json = JSON.stringify(to_data);  
    websocket.send(json);  

}

/*发送群聊天消息*/
function chat_group(group_id,content){
    var to_data = {  
        'cmd':'chat_group',
        'data':{
            'group_id':group_id,
            'content':content,
        }
    };  
    var json = JSON.stringify(to_data);  
    websocket.send(json); 
}

/*删除未读的消息*/
function del_group_chat_unread(group_id){
    var to_data = {  
        'cmd':'del_group_chat_unread',
        'data':{
            'group_id':group_id,
        }
    };  
    var json = JSON.stringify(to_data);  
    websocket.send(json); 
}

/*获取未读的消息*/
function get_group_chat_unread(group_id){
    var to_data = {  
        'cmd':'get_group_chat_unread',
        'data':{
            'group_id':group_id,
        }
    };  
    var json = JSON.stringify(to_data);  
    websocket.send(json); 
    del_group_chat_unread(group_id);
}


/*websocket*/
function ws_init(url) {  
    websocket = new WebSocket(url);  
    websocket.onopen = function (evt) {  
        is_conn = true;  
        debug("连接了服务端");  
        login();

    };  

    websocket.onclose = function (evt) {  
        is_conn = false;  
        debug("断开了服务端");  
    };  

    websocket.onmessage = function (evt) {  
        debug('接收服务端的消息：' + evt.data);  
        var data = JSON.parse(evt.data);  

        switch (data.cmd) {  
            case 'login':  
                if(data.status!=1){
                    debug("登陆失败");
                    tip(data.msg);
                    return;
                }

                update_user("在线"+"  "+data.data.name);
                tip('欢迎'+data.data.name);

                /*好友列表*/
                get_friend();
                get_group();
                break;  
            case 'add_friend':  
                if(data.status!=1){
                    debug("添加好友失败");  
                    tip(data.msg);
                    return;
                }

                tip('添加好友成功');
                get_friend();
                break;  
            case 'get_friend':
            	if(data.status==1){
                   chat_get_friend(data.data);
                }
                break;
            case 'get_chat_friend_to':  
                if(data.status==1 && chat_iframe_win){
                    var chat_user_id = chat_iframe_win.get_chat_user_id();
                    if(chat_user_id == data.data.from_user_id){
                        chat_iframe_win.add_user_chat_content_to(data.data);
                        del_chat_unread(chat_user_id);
                    }
                }
                break;
            case 'get_chat_friend_from':  
                if(data.status==1){
                    chat_iframe_win.add_user_chat_content_from(data.data);
                }
                break;  
            case 'update_unread_num':  
                if(data.status==1){
                    if($.inArray(data.data.user_id,chat_user_ids)==-1){
                        list_iframe_win.update_unread_num(data.data.user_id,data.data.num);
                    }
                    if(chat_iframe_win){
                        var chat_user_id = chat_iframe_win.get_chat_user_id();
                        if($.inArray(data.data.user_id,chat_user_ids)!=-1 && chat_user_id!=data.data.user_id){
                            list_iframe_win.update_unread_num(data.data.user_id,data.data.num);
                        }
                    }
                    
                }
                break; 
            case 'get_chat_unread':  
                if(data.status==1){
                    chat_iframe_win.get_unread_content(data.data);
                }
                break; 
            case 'create_group':  
                if(data.status==1){
                    get_group();
                    tip('创建成功');
                }
                break;    
            case 'get_group':
                if(data.status==1){
                   chat_get_group(data.data);
                }
                break;
            case 'add_group':
                if(data.status==1){
                   get_group();
                   tip('添加成功');
                }
                break;
            case 'get_chat_group_to':  
                if(data.status==1 && chat_group_iframe_win){
                    var chat_group_id = chat_group_iframe_win.get_chat_group_id();
                    if(chat_group_id == data.data.group_id){
                        chat_group_iframe_win.add_group_chat_content_to(data.data);
                        del_group_chat_unread(chat_group_id);
                    }
                }
                break;
            case 'get_chat_group_from':  
                if(data.status==1){
                    chat_group_iframe_win.add_group_chat_content_from(data.data);
                }
                break;  
            case 'update_group_unread_num':  
                if(data.status==1){
                    if($.inArray(data.data.group_id,chat_group_ids)==-1){
                        list_iframe_win.update_group_unread_num(data.data.group_id,data.data.num);
                    }
                    if(chat_group_iframe_win){
                        var chat_group_id = chat_group_iframe_win.get_chat_group_id();
                        if($.inArray(data.data.group_id,chat_group_ids)!=-1 && chat_group_id!=data.data.group_id){
                            list_iframe_win.update_group_unread_num(data.data.group_id,data.data.num);
                        }
                    }
                    
                }
                break; 
            case 'get_group_chat_unread':  
                if(data.status==1){
                    chat_group_iframe_win.get_group_unread_content(data.data);
                }
                break;
        }  
    };  

    websocket.onerror = function (evt, e) {  
        debug('错误：' + evt.data);  
    };  
}  




function sendMsg() {  
    var send_msg = $("#b").val();  
    if (send_msg) {  
        var login_json = {  
            'code': 2,  
            'msg': '',  
            'data': [{  
                'from': me_id,  
                'from_name':usr,  
                'to': send_id,  
                'to_name':send_name,  
                'msg': send_msg  
            }]  
        };  

        var json = JSON.stringify(login_json);  

        websocket.send(json);  

        create_chat(false, "","@"+send_name+" "+send_msg);  

        $("#b").val("");  
    } else {  
        alert("please input something");  
    }  
}  

function create_friend_item(data) {  
    $(".friend_item li").remove();  

    $(".friend_item").append("<li><a href='javascript:void(0)' id='0'>all</a></li>");  


    for (var i = 0; i < data.length; i++) {  
        for (var key in data[i]) {  
            if (parseInt(key) !== parseInt(me_id)) {  
                $(".friend_item").append("<li><a href='javascript:void(0)' id='" + key + "'>" + data[i][key] + "</a></li>");  
            }  
        }  
    }  


    $(".friend_item li a").click(function () {  
        $(".send_obj").text($(this).text());  
        send_id = parseInt($(this).attr("id"));  
        send_name = $(this).text();  
    });  
}  
 //聊天后创建聊天消息列表</span>  
function create_chat(left, title,msg) {  
    if (left) {  
        $("tbody").append("<tr><td class='td_left'>"+title+"<span class='span_left'>" + msg + "</span></td>/tr>")  
    } else {  
        $("tbody").append("<tr><td class='td_right'>"+title+"<span class='span_right'>" + msg + "</span></td>/tr>")  
    }  

    var root = document.getElementsByClassName("chat_right_table");  
    //root.scrollIntoView(true);  
}  

 //展示哪一个面板</span>  
function switch_tab(res) {  
    all_none();  
    switch (res) {  
        case 1:  
            $(".register").css("display", 'block');  
            break;  
        case 2:  
            $(".login ").css("display", 'block');  
            break;  
        case 3:  
            $(".chat_div").css("display", 'flex');  
            break;  
        case 4:  
            $(".error").css("display", 'block');  
            break;  
    }  
}  
 //隐藏所以面板</span>  
function all_none() {  
    $(".register").css("display", 'none');  
    $(".login").css("display", 'none');  
    $(".chat_div").css("display", 'none');  
    $(".error").css("display", 'none');  
}  

function re_conn() {  
    i++;  
    $(".error_tips").text(i);  
    init();  
}  

//捕获文档对象的按键弹起事件  
$(document).keyup(function (e) {  
    //按键信息对象以参数的形式传递进来了  
    if (e.keyCode === 13) {  
        //回车后  
        sendMsg();  
    }  
});  



