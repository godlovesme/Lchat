<?php
/**
 * REDIS 操作
 */
namespace Chat;

class CRedis{

	public $redis = null;

	public function __construct($ip,$port)
	{
		$this->redis = new \Redis();  
        $this->redis->connect($ip, $port);  
	}

	/*获取用户登陆session*/
	public function getUserIdBySid($sid){
		return $this->redis->hget('user|login',$sid);
	}

	/*获取用户信息*/
	public function getUserInfo($user_id){
		$info = $this->redis->hmget('user|'.$user_id,array(
			'id','name','birthday','email','phone','face'
		));
		if(empty($info['id'])){
			return array();
		}
		return $info;
	}

	/*获取用户fd*/
	public function setUserFd($user_id,$fd){
		$this->redis->hset('fd|user',$fd,$user_id);
        $this->redis->hset('user|fd',$user_id,$fd);
	}

	/*清空用户fd*/
	public function clearUserFd(){
        $this->redis->del('fd|user');
        $this->redis->del('user|fd');
	}

	/*获取用户ID*/
	public function getUserIdByFd($fd){
		return $this->redis->hget('fd|user',$fd);
	}

	/*获取用户fd*/
	public function getUserFdByUserId($user_id){
		return $this->redis->hget('user|fd',$user_id);
	}
	
	/*添加好友*/
	public function addFriend($user_id,$to_user_id){
		/*我的好友*/
    	$this->redis->sadd('user|'.$user_id."|friends",$to_user_id);
        /*对方好友*/
        $this->redis->sadd('user|'.$to_user_id."|friends",$user_id);
	}

	/*我的好友列表*/
	public function getFriends($user_id){

		$friend_id_arr = $this->redis->smembers('user|'.$user_id."|friends");

		$out = array();
		foreach ($friend_id_arr as $friend_id) {
			$friend_info = $this->getUserInfo($friend_id);
			if(!empty($friend_info)){
				$out[] = $friend_info;
			}
		}

		return $out;
	}

	/*我的好友列表ID*/
	public function getFriendIds($user_id){
		$out = $this->redis->smembers('user|'.$user_id."|friends");
		return $out;
	}

	/*获取用户聊天的key*/
	public function getUserChatInfoKey($user_id,$to_user_id){
		$key1 = "chat|user|".date('Ym')."|{$to_user_id}|{$user_id}";
		$key2 = "chat|user|".date('Ym')."|{$user_id}|{$to_user_id}";
		
		$key = $key1;
		$res1 = $this->redis->exists($key1);
		if($res1!=1){
			$res2 = $this->redis->exists($key2);
			if($res2==1){
				$key = $key2;
			}
		}
		return $key;
	}

	/*添加用户聊天信息*/
	public function addUserChatInfo($user_id,$to_user_id,$content){
		$key = $this->getUserChatInfoKey($user_id,$to_user_id);
		$time = time();
		$data = array(
			"from_user_id"=>$user_id,
			"to_user_id"=>$to_user_id,
			"content"=>$content,
			'add_time'=>$time
		);
		$this->redis->zAdd($key,$time,json_encode($data));
		return $data;
	}

	/*添加未读消息*/
	public function addUnreadChatInfo($user_id,$to_user_id,$content){
		$this->redis->zAdd("chat|user|unread|{$to_user_id}",$user_id,json_encode(array(
			"from_user_id"=>$user_id,
			"to_user_id"=>$to_user_id,
			"content"=>$content,
			'add_time'=>time()
		)));
	}

	/*获取未读数量*/
	public function getUnreadChatInfoNum($user_id,$to_user_id){
		return $this->redis->zcount("chat|user|unread|{$to_user_id}",$user_id,$user_id);
	}

	/*获取未读消息*/
	public function getUnreadChatInfo($user_id,$to_user_id){
		$res = $this->redis->zrangebyscore("chat|user|unread|{$to_user_id}",$user_id,$user_id);
		if(!$res) return array();
		$out = array();
		foreach ($res as $vo) {
			$out[] = json_decode($vo,true);
		}
		$out = \Chat\Util::list_sort_by($out,'add_time','asc');
		return $out;
	}

	/*删除未读消息*/
	public function delUnreadChatInfo($user_id,$to_user_id){
		$this->redis->zremrangebyscore("chat|user|unread|{$to_user_id}",$user_id,$user_id);
	}

	/*创建群*/
	public function createGroup($user_id,$group_name){

		$this->redis->hincrby('config','group_id',1);
	    $group_id = $this->redis->hget('config','group_id');

		$this->redis->hmset('group|'.$group_id,array(
			'id'=>$group_id,
			'user_id'=>$user_id,
			'name'=>$group_name,
			'add_time'=>time()
		));
		/*用户创建的群*/
		$this->redis->sadd("group|create|".$user_id,$group_id);
		/*用户加入群*/
		$this->addUserGroup($user_id,$group_id);
		/*群里的用户*/
		$this->addGroupUser($group_id,$user_id);

		$out = $this->getGroupById($user_id,$group_id);
		return $out;
	}

	/*获取群*/
	public function getGroupById($group_id){
		return $this->redis->hgetall('group|'.$group_id);
	}

	public function addGroup($group_id,$user_id){
		/*用户加入群*/
		$this->addUserGroup($user_id,$group_id);
		/*群里的用户*/
		$this->addGroupUser($group_id,$user_id);
	}

	/*加入群*/
	public function addUserGroup($user_id,$group_id){
    	$this->redis->sadd('group|add|'.$user_id,$group_id);
	}

	/*群里的成员*/
	public function addGroupUser($group_id,$user_id){
		$this->redis->sadd('group|in|'.$group_id,$user_id);
	}

	/*获取我加入的群*/
	public function getUserGroup($user_id){
		$group_res = $this->redis->smembers('group|add|'.$user_id);

		$out = array();
		foreach ((array)$group_res as $group_id) {
			$out[] = $this->getGroupById($group_id);
		}
		return $out;
	}

	/*获取群里成员信息*/
	public function getGroupUserInfo($group_id){
		$user_res = $this->redis->smembers('group|in|'.$group_id);

		$out = array();
		foreach ((array)$user_res as $user_id) {
			$out[] = $this->getUserInfo($user_id);
		}
		return $out;
	}

	/*获取群里成员FD*/
	public function getGroupUserFd($group_id){
		$user_res = $this->redis->smembers('group|in|'.$group_id);
		$out = array();
		foreach ((array)$user_res as $user_id) {
			$out[$user_id] = $this->getUserFdByUserId($user_id);
		}
		return $out;
	}

	/*添加群聊天信息*/
	public function addGroupChatInfo($group_id,$user_id,$content){
		$key = "chat|group|".date('Ym')."|{$group_id}";
		$time = time();
		$data = array(
			"group_id"=>$group_id,
			"user_id"=>$user_id,
			"content"=>$content,
			'add_time'=>$time
		);
		$this->redis->zAdd($key,$time,json_encode($data));
		return $data;
	}


	/*添加群未读消息*/
	public function addUnreadGroupChatInfo($user_id,$group_id,$content){
		$this->redis->zAdd("chat|group|unread|{$user_id}",$group_id,json_encode(array(
			"user_id"=>$user_id,
			"group_id"=>$group_id,
			"content"=>$content,
			'add_time'=>time()
		)));
	}

	/*获取群未读数量*/
	public function getUnreadGroupChatInfoNum($user_id,$group_id){
		return $this->redis->zcount("chat|group|unread|{$user_id}",$group_id,$group_id);
	}

	/*获取群未读消息*/
	public function getUnreadGroupChatInfo($user_id,$group_id){
		$res = $this->redis->zrangebyscore("chat|group|unread|{$user_id}",$group_id,$group_id);
		if(!$res) return array();
		$out = array();
		foreach ($res as $vo) {
			$temp = json_decode($vo,true);
			$temp['user'] = $this->getUserInfo($temp['user_id']);
			$out[] = $temp;
		}
		$out = \Chat\Util::list_sort_by($out,'add_time','asc');
		return $out;
	}

	/*删除群未读消息*/
	public function delUnreadGroupChatInfo($user_id,$group_id){
		$this->redis->zremrangebyscore("chat|group|unread|{$user_id}",$group_id,$group_id);
	}



}


