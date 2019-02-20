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
        var_dump($this->userInfo['username']);
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
    public function updateImg(){
        if(request()->isPost()) {
            $data = input('post.');
            $validate= Validate::make([
                'username'=>'require',
                'img' => 'require'
            ]);
            if(!$validate->check($data)){
                return json(['code' => -1, 'msg' => '参数异常']);
            }
            $result = db('users')
                ->where(array('username'=>$data['username']))
                ->update(array('img'=>$data['img']));
            if($result){
                return json(['code' => 1, 'msg' => '保存成功']);
            }
            return json(['code' => -1, 'msg' => '保存失败']);
        }else{
            return json(['code' => -1, 'msg' => '参数异常']);
        }
    }
}