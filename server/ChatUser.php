<?php

namespace Chat;

class ChatUser
{
	/*与好友聊天*/
    public static function friend($chat){

    	$data = json_decode($chat->frame->data, true);  
    	$fd = $chat->frame->fd;

        $user_id = intval($data['data']['user_id']);
        $content = $data['data']['content'];
        if($user_id<=0){
            $chat->send(array('cmd'=>"chat_friend",'status'=>-1,'msg'=>"用户ID有误")); 
            return;
        }
        if(empty($content)){
            $chat->send(array('cmd'=>"chat_friend",'status'=>-1,'msg'=>"内容为空")); 
            return;
        }
        /*用户不存在*/
        $userInfo = $chat->redis->getUserInfo($user_id);
        if(empty($userInfo)){
            $chat->send(array('cmd'=>"chat_friend",'status'=>-2,'msg'=>"用户不存在")); 
            return;
        }  


        /*根据用户的fd获取用户ID*/
        $_user_id = $chat->redis->getUserIdByFd($chat->frame->fd);
        $_user_info = $chat->redis->getUserInfo($_user_id);

        /*获取用户的fd*/
        $user_fd = $chat->redis->getUserFdByUserId($user_id);

        /*添加聊天记录*/
        $to_data = $chat->redis->addUserChatInfo($_user_id,$user_id,$content);

        $to_data['add_time'] = date('Y-m-d H:i',$to_data['add_time']);
        $to_data['from_user_name'] = $_user_info['name'];
        $to_data['from_user_face'] = $_user_info['face'];
        $to_data['to_user_name'] = $userInfo['name'];
        $to_data['to_user_face'] = $userInfo['face'];
       
        /*发给我方*/
        $chat->send(array('cmd'=>"get_chat_friend_from",'status'=>1,'data'=>$to_data)); 

        if(!empty($user_fd)){
            /*对方在线*/
            /*发给对方*/
            $chat->send(array('cmd'=>"get_chat_friend_to",'status'=>1,'data'=>$to_data),$user_fd); 

            $chat->redis->addUnreadChatInfo($_user_id,$user_id,$content);

            $num = $chat->redis->getUnreadChatInfoNum($_user_id,$user_id);

            $chat->send(array('cmd'=>"update_unread_num",'status'=>1,'data'=>array(
                'user_id'=>$_user_id,
                'num'=>$num
            )),$user_fd);

        }else{
            /*对方不在线*/
            /*添加未读聊天*/
            $chat->redis->addUnreadChatInfo($_user_id,$user_id,$content);

        }

    }

    /*用户未读消息*/
    public static function read($chat){
        $data = json_decode($chat->frame->data, true);  
        $fd = $chat->frame->fd;

        $user_id = intval($data['data']['user_id']);
       
        if($user_id<=0){
            $chat->send(array('cmd'=>"get_chat_unread",'status'=>-1,'msg'=>"用户ID有误")); 
            return;
        }
        /*用户不存在*/
        $userInfo = $chat->redis->getUserInfo($user_id);
        if(empty($userInfo)){
            $chat->send(array('cmd'=>"get_chat_unread",'status'=>-2,'msg'=>"用户不存在")); 
            return;
        }

        $_user_id = $chat->redis->getUserIdByFd($chat->frame->fd);

        $unread_list = $chat->redis->getUnreadChatInfo($user_id,$_user_id);

        foreach ($unread_list as $key => $value) {
            $unread_list[$key]['add_time'] = date('Y-m-d H:i',$value['add_time']);
            $unread_list[$key]['from_user_name'] = $userInfo['name'];
            $unread_list[$key]['from_user_face'] = $userInfo['face'];
        }
        
        $chat->redis->delUnreadChatInfo($user_id,$_user_id);
        $chat->send(array('cmd'=>"get_chat_unread",'status'=>1,'data'=>$unread_list)); 


        
    }

    /*删除未读消息*/
    public static function del($chat){
        $data = json_decode($chat->frame->data, true);  
        $fd = $chat->frame->fd;

        $user_id = intval($data['data']['user_id']);
       
        if($user_id<=0){
            $chat->send(array('cmd'=>"del_chat_unread",'status'=>-1,'msg'=>"用户ID有误")); 
            return;
        }
        /*用户不存在*/
        $userInfo = $chat->redis->getUserInfo($user_id);
        if(empty($userInfo)){
            $chat->send(array('cmd'=>"del_chat_unread",'status'=>-2,'msg'=>"用户不存在")); 
            return;
        }
        $_user_id = $chat->redis->getUserIdByFd($chat->frame->fd);
        $chat->redis->delUnreadChatInfo($user_id,$_user_id);

        $num = $chat->redis->getUnreadChatInfoNum($_user_id,$user_id);
        $chat->send(array('cmd'=>"update_unread_num",'status'=>1,'data'=>array(
            'user_id'=>$user_id,
            'num'=>$num
        )));
    }
}