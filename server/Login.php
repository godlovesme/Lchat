<?php

namespace Chat;

class Login
{
	/*登陆处理*/
    public static function deal($chat){

    	$data = json_decode($chat->frame->data, true);  
    	$fd = $chat->frame->fd;

    	$user_id = $chat->redis->getUserIdBySid($data['data']['sid']); 


    	if(!$user_id){
    		$chat->send(array('cmd'=>"login",'status'=>-1,'msg'=>"未登陆")); 
            return;
    	}
    	
        $userInfo = $chat->redis->getUserInfo($user_id);
        if(empty($userInfo)){
            $chat->send(array('cmd'=>"login",'status'=>-2,'msg'=>"未注册"));    
            return;
        }

        /*设置用户fd*/
        $chat->redis->setUserFd($user_id,$chat->frame->fd);

    	$chat->send(array('cmd'=>"login",'status'=>1,'data'=>array(
            'name'=>$userInfo['name'],
            'birthday'=>$userInfo['birthday'],
            'email'=>$userInfo['email'],
            'phone'=>$userInfo['phone'],
            'face'=>'image/'.$userInfo['face'].'.png'
        ))); 


    }
}