<?php
namespace app\user\controller;
use think\Input;
use think\Controller;
class Common extends Controller{
    protected $userInfo;
    protected $redis;
    protected $redis_status = false;//redis状态
    protected $redis_timeout = 20;//redis状态
    public function initialize(){
        if (!session('user.id')) {
            $this->redirect('login/index');
        }
        $this->userInfo=db('users')->alias('u')
            ->join(config('database.prefix').'user_level ul','u.level = ul.level_id','left')
            ->where('u.id','=',session('user.id'))
            ->field('u.*,ul.level_name')
            ->find();

        if(config('is_redis')) {
            $this->redis = new \Redis();
            $this->redis->connect(
                config('redis')['host']
                ,config('redis')['port']
            );
            $this->redis->auth(config('redis')['password']);
            $this->redis->setOption(\Redis::OPT_PREFIX,config('redis')['prefix']);
            $this->redis_status = true;
            $this->redis_timeout = config('redis')['timeout'] ?: 20;
            // $this->redis->flushall();exit;
        }

        $this->assign('userInfo',$this->userInfo);
    }
    public function _empty(){
        return $this->error('空操作，返回上次访问页面中...');
    }
    //退出登陆
    public function logout(){
        session('user',null);
        $this->redirect('login/index');
    }

    protected function getSerializeData(array $key, $act = 'set', $func){
        $data = null;
        if($this->redis_status == true){
            switch ($act){
                case 'set':
                    if(!$this->redis->exists($key[0])){
                        $data = $func();
                        $this->redis->set($key[0], serialize($data), $this->redis_timeout);
                    }else{
                        $data = unserialize($this->redis->get($key[0]));
                    }
                    break;
                case 'hset':
                    if(!$this->redis->hExists($key[0], $key[1])){
                        $data = $func();
                        $this->redis->hset($key[0], $key[1], serialize($data));
                        $this->redis->expire($key[0], $this->redis_timeout);
                    }
                    else{
                        $data = unserialize($this->redis->hget($key[0], $key[1]));
                    }
                    break;
            }
        }else{
            $data = $func();
        }

        return $data;
    }
}