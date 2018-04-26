<?php

namespace Chat;

class Friend
{
	/*添加好友处理*/
    public static function add($chat){

    	$data = json_decode($chat->frame->data, true);  
    	$fd = $chat->frame->fd;

        $user_id = intval($data['data']['user_id']);
        if($user_id<=0){
            $chat->send(array('cmd'=>"add_friend",'status'=>-1,'msg'=>"用户ID有误")); 
            return;
        }

        /*用户不存在*/
        $userInfo = $chat->redis->getUserInfo($user_id);
        if(empty($userInfo)){
            $chat->send(array('cmd'=>"add_friend",'status'=>-2,'msg'=>"用户不存在")); 
            return;
        }

        /*根据用户的fd获取用户ID*/
        $_user_id = $chat->redis->getUserIdByFd($chat->frame->fd);

        if($user_id==$_user_id){
            $chat->send(array('cmd'=>"add_friend",'status'=>-3,'msg'=>"不能加自己为好友")); 
            return;
        }


        $friendsIds = $chat->redis->getFriendIds($_user_id);

        if(in_array($user_id, $friendsIds)){
            $chat->send(array('cmd'=>"add_friend",'status'=>-4,'msg'=>"已经是好友了")); 
            return;
        }

        $chat->redis->addFriend($_user_id,$user_id);

        $chat->send(array('cmd'=>"add_friend",'status'=>1)); 

        $user_fd = $chat->redis->getUserFdByUserId($user_id);
        $chat->send(array('cmd'=>"add_friend",'status'=>1),$user_fd); 

    }
    /*获取好友列表*/
    public static function get($chat){
        $data = json_decode($chat->frame->data, true);  
        $fd = $chat->frame->fd;

        $_user_id = $chat->redis->getUserIdByFd($chat->frame->fd);

        $user_list = $chat->redis->getFriends($_user_id);

        $out = array();
        $out[] = array(
            'title'=>'我的好友',
            'total_num'=>count($user_list),
            'user_list'=>$user_list
        );
        $chat->send(array('cmd'=>"get_friend",'status'=>1,'data'=>$out));

        /*好友的未读消息*/
        foreach ($user_list as $user_info) {
            $id = $user_info['id'];
            $num = $chat->redis->getUnreadChatInfoNum($id,$_user_id);
            if($num>0){
                $chat->send(array('cmd'=>"update_unread_num",'status'=>1,'data'=>array(
                    'user_id'=>$id,
                    'num'=>$num
                )));
            }
            sleep(0.01);
            
        }

        
       
    }
}