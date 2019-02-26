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

        $img2 = $this->redis->get('img2-'.$this->userInfo['username']);
        $this->assign('img2',$img2);
        return $this->fetch();
    }

    public function getImg(){
        session_write_close();
//        if($this->userInfo['img']){
//            return json(['code' => 1, 'msg' => 'success', 'data'=>$this->userInfo['img']]);
//        }
        //var_dump($this->userInfo['username']);
        //不存在
        $data = \input('post.');
        if(!$data['num'] || $data['num'] > 100){
            return json(['code' => -1, 'msg' => '参数错误']);
        }
        $this->redis->rPush('users',$this->userInfo['username'].'^^'.$data['num']);
        sleep(30);
//        $img=db('users')
//            ->where('id','=',session('user.id'))
//            ->field('img')
//            ->find();
        $img = $this->redis->get('img-'.$this->userInfo['username']);
        if($img){
            return json(['code' => 1, 'msg' => 'success', 'data'=>$img]);
        }else{
            return json(['code' => -1, 'msg' => '获取失败']);
        }
    }


}