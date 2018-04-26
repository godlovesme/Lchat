<?php

namespace Chat;

class ChatGroup
{
	/*聊天*/
    public static function say($chat){

    	$data = json_decode($chat->frame->data, true);  
    	$fd = $chat->frame->fd;

        $group_id = intval($data['data']['group_id']);
        $content = $data['data']['content'];
        if($group_id<=0){
            $chat->send(array('cmd'=>"chat_group",'status'=>-1,'msg'=>"群ID有误")); 
            return;
        }
        if(empty($content)){
            $chat->send(array('cmd'=>"chat_group",'status'=>-1,'msg'=>"内容为空")); 
            return;
        }
        /*群不存在*/
        $groupInfo = $chat->redis->getGroupById($group_id);
        if(empty($groupInfo)){
            $chat->send(array('cmd'=>"chat_group",'status'=>-2,'msg'=>"群不存在")); 
            return;
        }  

        /*根据用户的fd获取用户ID*/
        $_user_id = $chat->redis->getUserIdByFd($chat->frame->fd);
        $_user_info = $chat->redis->getUserInfo($_user_id);

        /*获取群中所有用户fd*/
        $group_user_fd = $chat->redis->getGroupUserFd($group_id);
        foreach ((array)$group_user_fd as $user_id=>$user_fd) {
            
            /*添加聊天记录*/
            $to_data = $chat->redis->addGroupChatInfo($group_id,$user_id,$content);

            $to_data['add_time'] = date('Y-m-d H:i',$to_data['add_time']);
            $to_data['user_name'] = $_user_info['name'];
            $to_data['user_face'] = $_user_info['face'];
           

            if($user_id==$_user_id){
                /*发给我方*/
                $chat->send(array('cmd'=>"get_chat_group_from",'status'=>1,'data'=>$to_data)); 
                continue;
            }

            if(!empty($user_fd)){
                /*对方在线*/
                /*发给对方*/
                $chat->send(array('cmd'=>"get_chat_group_to",'status'=>1,'data'=>$to_data),$user_fd); 

                $chat->redis->addUnreadGroupChatInfo($user_id,$group_id,$content);

                $num = $chat->redis->getUnreadGroupChatInfoNum($user_id,$group_id);

                $chat->send(array('cmd'=>"update_group_unread_num",'status'=>1,'data'=>array(
                    'group_id'=>$group_id,
                    'num'=>$num
                )),$user_fd);

            }else{
                /*对方不在线*/
                /*添加未读聊天*/
                $chat->redis->addUnreadGroupChatInfo($user_id,$group_id,$content);

            }

            sleep(0.01);
        }

       
        
    }

    /*用户未读消息*/
    public static function read($chat){
        $data = json_decode($chat->frame->data, true);  
        $fd = $chat->frame->fd;

        $group_id = intval($data['data']['group_id']);
       
        if($group_id<=0){
            $chat->send(array('cmd'=>"get_group_chat_unread",'status'=>-1,'msg'=>"群ID有误")); 
            return;
        }
        /*用户不存在*/
        $groupInfo = $chat->redis->getGroupById($group_id);
        if(empty($groupInfo)){
            $chat->send(array('cmd'=>"get_chat_unread",'status'=>-2,'msg'=>"群不存在")); 
            return;
        }


        $_user_id = $chat->redis->getUserIdByFd($chat->frame->fd);

        $unread_list = $chat->redis->getUnreadGroupChatInfo($_user_id,$group_id);

        foreach ($unread_list as $key => $value) {
            $unread_list[$key]['add_time'] = date('Y-m-d H:i',$value['add_time']);
            $unread_list[$key]['user_name'] = $value['user']['name'];
            $unread_list[$key]['user_face'] = $value['user']['face'];
        }

        $chat->redis->delUnreadGroupChatInfo($_user_id,$group_id);
        $chat->send(array('cmd'=>"get_group_chat_unread",'status'=>1,'data'=>$unread_list)); 


        
    }

    /*删除未读消息*/
    public static function del($chat){
        $data = json_decode($chat->frame->data, true);  
        $fd = $chat->frame->fd;

        $group_id = intval($data['data']['group_id']);
       
        if($group_id<=0){
            $chat->send(array('cmd'=>"del_group_chat_unread",'status'=>-1,'msg'=>"用户ID有误")); 
            return;
        }

        /*群不存在*/
        $groupInfo = $chat->redis->getGroupById($group_id);
        if(empty($groupInfo)){
            $chat->send(array('cmd'=>"del_group_chat_unread",'status'=>-2,'msg'=>"用户不存在")); 
            return;
        }

        $_user_id = $chat->redis->getUserIdByFd($chat->frame->fd);
        $chat->redis->delUnreadGroupChatInfo($_user_id,$group_id);

        $num = $chat->redis->getUnreadGroupChatInfoNum($_user_id,$group_id);

        $chat->send(array('cmd'=>"update_group_unread_num",'status'=>1,'data'=>array(
            'group_id'=>$group_id,
            'num'=>$num
        )));

    }


}