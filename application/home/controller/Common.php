<?php
namespace app\home\controller;
use think\Db;
use clt\Leftnav;
use think\Controller;
class Common extends Controller{

    protected $pagesize,$changyan;
    protected $redis;
    protected $redis_status = false;//redis状态
    protected $redis_timeout = 20;//redis状态
    public function initialize(){
        $sys = cache('System');
        $this->assign('sys',$sys);
        if($sys['mobile']=='open'){
            if(isMobile()){
                $this->redirect('mobile/index/index');
            }
        }
        //获取控制方法
        $action = request()->action();
        $controller = request()->controller();
        $this->assign('action',($action));
        $this->assign('controller',strtolower($controller));
        define('MODULE_NAME',strtolower($controller));
        define('ACTION_NAME',strtolower($action));
        //导航
        $thisCat = Db::name('category')->where('id',input('catId'))->find();
        $this->assign('title',$thisCat['title']);
        $this->assign('keywords',$thisCat['keywords']);
        $this->assign('description',$thisCat['description']);
        define('DBNAME',strtolower($thisCat['module']));
        $this->pagesize = $thisCat['pagesize']>0 ? $thisCat['pagesize'] : '';
        // 获取缓存数据
        $cate = cache('cate');

        if(!$cate){
            $column_one = Db::name('category')->where([['parentid','=',0],['ismenu','=',1]])->order('sort')->select();
            $column_two = Db::name('category')->where('ismenu',1)->order('sort')->select();
            $tree = new Leftnav ();
            $cate = $tree->index_top($column_one,$column_two);
            cache('cate', $cate, 3600);
        }
        $this->assign('category',$cate);
        //广告
        $adList = cache('adList');
        if(!$adList){
            $adList = Db::name('ad')->where(['type_id'=>1,'open'=>1])->order('sort asc')->limit('4')->select();
            cache('adList', $adList, 3600);
        }
        $this->assign('adList', $adList);
        //友情链接
        $linkList = cache('linkList');
        if(!$linkList){
            $linkList = Db::name('link')->where('open',1)->order('sort asc')->select();
            cache('linkList', $linkList, 3600);
        }
		$this->assign('linkList', $linkList);
        //畅言
        $plugin = db('plugin')->where(['code'=>'changyan'])->find();
        $this->changyan = unserialize($plugin['config_value']);
        $this->assign('changyan', $this->changyan);
        $this->assign('time', time());

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
    }
    public function _empty(){
        return $this->error('空操作，返回上次访问页面中...');
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