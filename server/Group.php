<?php

namespace Chat;

class Group
{
	/*创建群*/
    public static function create($chat){

    	$data = json_decode($chat->frame->data, true);  
    	$fd = $chat->frame->fd;

        $group_name = trim($data['data']['group_name']);
        if(empty($group_name)){
            $chat->send(array('cmd'=>"create_group",'status'=>-1,'msg'=>"群的名称为空")); 
            return;
        }

        /*根据用户的fd获取用户ID*/
        $_user_id = $chat->redis->getUserIdByFd($chat->frame->fd);

        
        $groupInfo = $chat->redis->createGroup($_user_id,$group_name);

        if(empty($groupInfo)){
            $chat->send(array('cmd'=>"create_group",'status'=>-2,'msg'=>"创建群失败")); 
        }

        $chat->send(array('cmd'=>"create_group",'status'=>1)); 

    }

    /*加入群*/
    public static function add($chat){
        $data = json_decode($chat->frame->data, true);  
        $fd = $chat->frame->fd;

        $group_id = trim($data['data']['group_id']);
        if(empty($group_id)){
            $chat->send(array('cmd'=>"add_group",'status'=>-1,'msg'=>"群的ID为空")); 
            return;
        }

        $groupInfo = $chat->redis->getGroupById($group_id);
        if(empty($groupInfo)){
            $chat->send(array('cmd'=>"add_group",'status'=>-2,'msg'=>"群不存在")); 
            return;
        }

        /*根据用户的fd获取用户ID*/
        $_user_id = $chat->redis->getUserIdByFd($chat->frame->fd);

        $chat->redis->addGroup($group_id,$_user_id);

        $chat->send(array('cmd'=>"add_group",'status'=>1));


    }

    /*获取我加入的群*/
    public static function get($chat){
        $data = json_decode($chat->frame->data, true);  
        $fd = $chat->frame->fd;

        $_user_id = $chat->redis->getUserIdByFd($chat->frame->fd);

        $group_list = $chat->redis->getUserGroup($_user_id);

        $chat->send(array('cmd'=>"get_group",'status'=>1,'data'=>$group_list));

        /*群的未读消息*/
        foreach ($group_list as $group_info) {
            $id = $group_info['id'];
            $num = $chat->redis->getUnreadGroupChatInfoNum($_user_id,$id);
            if($num>0){
                $chat->send(array('cmd'=>"update_group_unread_num",'status'=>1,'data'=>array(
                    'group_id'=>$id,
                    'num'=>$num
                )));
            }
            sleep(0.01);
            
        }

    }




}