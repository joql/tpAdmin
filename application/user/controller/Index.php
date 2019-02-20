<?php
namespace app\user\controller;
use think\Cache;
use think\Input;
use think\Validate;

class Index extends Common{
    public function initialize(){
        parent::initialize();

    }
    public function index(){
        $this->assign('title','会员中心');
        return $this->fetch();
    }

    public function getImg(){

        if($this->userInfo['img']){
            return json(['code' => 1, 'msg' => 'success', 'data'=>$this->userInfo['img']]);
        }
        //var_dump($this->userInfo['username']);
        //不存在
        $this->redis->rPush('users',$this->userInfo['username']);
        sleep(5);
        $img=db('users')
            ->where('id','=',session('user.id'))
            ->field('img')
            ->find();
        if($img['img']){
            return json(['code' => 1, 'msg' => 'success', 'data'=>$img['img']]);
        }else{
            return json(['code' => 1, 'msg' => '获取失败']);
        }
    }

    public function getUsers(){
        if($this->redis->llen('users') >0){
            return json(['code' => 1, 'msg' => 'success', 'data'=>$this->redis->lpop('users')]);
        }else{
            return json(['code' => -1, 'msg' => '没有数据']);
        }
    }

}