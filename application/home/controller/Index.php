<?php
namespace app\home\controller;
use think\Db;
use clt\Lunar;
use think\facade\Env;
use think\Validate;

class Index extends Common{
    public function initialize(){
        parent::initialize();
    }
    public function index(){
        $this->redirect('/user/login/index');
        $order = input('order','createtime');
        $time = time();
        $list=db('article')->alias('a')
            ->join(config('database.prefix').'category c','a.catid = c.id','left')
            ->field('a.id,c.catdir,c.catname')
            ->order($order.' desc')
            ->where('createtime', '>', $time)
            ->limit('15')
            ->select();
        foreach ($list as $k=>$v){
            $list[$k]['time'] = toDate($v['createtime']);
            $list[$k]['url'] = url('home/'.$v['catdir'].'/info',array('id'=>$v['id'],'catId'=>$v['catid']));
        }
        $this->assign('list', $list);
        if(!isMobile()){
            $m= $thisDate = date("m");
            $d= $thisDate = date("d");
            $y= $thisDate = date("Y");
            $Lunar=new Lunar();
            //获取农历日期
            $nonliData = $Lunar->convertSolarToLunar($y,$m,$d);
            $nonliData = $nonliData[1].'-'.$nonliData[2];
            $feastId = db('feast')->where(array('feast_date'=>$nonliData,'type'=>2))->value('id');
            $style='';
            $js='';
            if($feastId){
                $element = db('feast_element')->where('pid',$feastId)->select();
                $style = '<style>';
                $js = '';
                foreach ($element as $k=>$v){
                    $style .= $v['css'];
                    $js .= $v['js'];
                }
                $style .= '</style>';

            }else{
                $feastId = db('feast')->where(array('feast_date'=>$m.'-'.$d,'type'=>1))->value('id');
                if($feastId){
                    $element = db('feast_element')->where('pid',$feastId)->select();
                    $style = '<style>';
                    $js = '';
                    foreach ($element as $k=>$v){
                        $style .= $v['css'];
                        $js .= $v['js'];
                    }
                    $style .= '</style>';
                }
            }
            $this->assign('style', $style);
            $this->assign('js', $js);
        }
        return $this->fetch();
    }
    public function senmsg(){
        $data = input('post.');
        $data['addtime'] = time();
        $data['ip'] = getIp();
        db('message')->insert($data);
        $result['status'] = 1;
        return $result;
    }
    public function down($id=''){
        $map['id'] = $id;
        $files = Db::name('download')->where($map)->find();
        return download(Env::get('root_path').'public'.$files['files'], $files['title']);
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
    public function getUserImg(){
        if(request()->isget()) {
            $data = input('get.');
            //var_dump($data);die();
            $validate= Validate::make([
                'username'=>'require',
            ]);
            if(!$validate->check($data)){
                return json(['code' => -1, 'msg' => '参数异常']);
            }
            $img = $this->getSerializeData(
                ['user-img-'.$data['username']]
                ,'set'
                ,function () use ($data){
                return db('users')
                    ->where('username','=',$data['username'])
                    ->value('img');
            }
            );
            if($img){
                return json(['code' => 1, 'msg' => 'success', 'data'=>$img]);
            }else{
                return json(['code' => 1, 'msg' => '获取失败']);
            }
        }else{
            return json(['code' => -1, 'msg' => '参数异常']);
        }
    }
}