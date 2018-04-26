<?php
/**
 * 服务端
 */



class WebsocketChat {
    public $server;
    public $redis = null;
    public $frame = null;
    public function __construct() {

        $this->redis = new \Chat\CRedis('192.168.1.158', 7000);  

        /*清空用户FD*/
        $this->redis->clearUserFd();

        $this->server = new swoole_websocket_server("0.0.0.0", 9501);

        $this->server->set(array(
            'open_length_check' => true,
            'package_max_length' => 81920, //1024*80
             // 'daemonize' => true, //是否作为守护进程  
            'worker_num' => 4, # 4个worker
        ));

        $this->server->on('open', function (swoole_websocket_server $server, $request) {
             echo "server: handshake success with fd{$request->fd}\n";
        });

        $this->server->on('message', function (swoole_websocket_server $server, $frame) {

            echo "Message: {$frame->data}\n";  
			echo "fd: {$frame->fd}\n";  

            $this->frame = $frame;
            $data = json_decode($frame->data, true);  
			switch ($data['cmd']) {  
				case 'login':  
                    \Chat\Login::deal($this);
				break;  
                case 'add_friend':
                    \Chat\Friend::add($this);
                break;
                case 'get_friend':
                    \Chat\Friend::get($this);
                break;
                case 'chat_friend':
                    \Chat\ChatUser::friend($this);
                break;
                case 'get_chat_unread':
                    \Chat\ChatUser::read($this);
                break;
                case 'del_chat_unread':
                    \Chat\ChatUser::del($this);
                break;
                case 'create_group':
                    \Chat\Group::create($this);
                break;
                case 'get_group':
                    \Chat\Group::get($this);
                break;
                case 'add_group':
                    \Chat\Group::add($this);
                break; 
                case 'chat_group':
                    \Chat\ChatGroup::say($this);
                break;
                case 'del_group_chat_unread':
                    \Chat\ChatGroup::del($this);
                break;
                case 'get_group_chat_unread':
                    \Chat\ChatGroup::read($this);
                break;
				default:  
				break;  
			}  


        });

        $this->server->on('close', function ($ser, $fd) {
            echo "client {$fd} closed\n";
        });

        $this->server->start();
    }

    /*发送消息*/
    public function send($msg,$fd=false,$is_all=false)  
    {  
        if(is_array($msg)){
            $msg = json_encode($msg);
        }
        if ($is_all) {  
            foreach ($this->server->connections as $fd) {  
                $this->server->push($fd, $msg);  
            }  
        } else {  
            if($fd){
                $this->server->push($fd, $msg);  
            }else{
                $this->server->push($this->frame->fd, $msg);  
            }
        }  
    }


}

/*自动加载*/
spl_autoload_register(function ($class) { 
    $class_arr = explode("\\", $class);
    $file = end($class_arr).".php";
    if (file_exists($file)) {
        include $file;
    }
});


new WebsocketChat();