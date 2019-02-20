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
}