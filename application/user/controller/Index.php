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
        $auto_login = 'http://api.foword.cn/user/login/auto.html?name='.$this->userInfo['username'].'&key='.md5($this->userInfo['username'].$this->userInfo['password']);
        $this->assign('auto_login', $auto_login);
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
        $validate = Validate::make([
            'num' => 'between:1,100',
            't_type' => 'require',
        ]);
        if(!$validate->check($data)){
            return json(['code' => -1, 'msg' => '参数错误']);
        }

        $this->redis->set('requset_list_'.$data['t_type'].'-'.$this->userInfo['username'],$this->userInfo['username'].'^^'.$data['num']);
        sleep(30);
        $img = $this->redis->get('img_'.$data['t_type'].'_'.$this->userInfo['username']);
        if($img){
            return json(['code' => 1, 'msg' => 'success', 'data'=>$img]);
        }else{
            return json(['code' => -1, 'msg' => '获取失败']);
        }
    }


}