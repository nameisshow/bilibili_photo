<?php

    class runExec{

        protected $db;
        protected $userAgent;
        protected $proxy;
        protected $param;
        protected $mycurl;

        protected $userArray;
        protected $photoArray;

        public function __construct()
        {
            $this->createPDO();
            $this->setParam();
            $this->formatParam($this->param);
            $this->getUserAgent();
            $this->initMyCurl();
        }

        protected function setParam(){
            $this->param = [
                'Photo'=>['cos','sifu'],
                //'Doc'=>['illustration','comic','draw'],
            ];

            $this->toLog(__FUNCTION__);
        }

        protected function createPDO(){
            $host = '127.0.0.1';
            $dbname = 'bi';
            $user = 'root';
            $passwd = 'root';
            $port = 3306;

            $dsn = "mysql:host=$host;dbname=$dbname;port=$port";
            $username = $user;
            $passwd = $passwd;

            try {
                $this->db = new \PDO($dsn, $username, $passwd);
            } catch (\Exception $e) {
                print_r($e->getMessage()) . "\r\n";
                exit();
            }

            $this->toLog(__FUNCTION__);
        }

        protected function initMyCurl(){
            require_once './curl.php';
            $this->mycurl = new myCurl();
            $this->mycurl->setHttps(false);

            $this->toLog(__FUNCTION__);
        }

        protected function getUserAgent(){
            $userAgent = require_once './userAgent.php';
            $this->userAgent = $userAgent;

            $this->toLog(__FUNCTION__);
        }

        protected function getProxy(){

        }

        protected function randomUserAgent(&$userAgent){
            $count = count($userAgent);
            return $userAgent[rand(1,$count)];
        }

        protected function formatParam($param){
            $res = [];
            foreach($param as $key=>$val){
                foreach($val as $k=>$v){
                    $res[] = [$key,$v];
                }
            }
            $this->param = $res;

            $this->toLog(__FUNCTION__);
        }

        protected function getUrl($photo,$category,$page,$type = 'hot'){
            //photo : Photo,Doc
            //category : cos,sifu,illustration,comic,draw,all
            //type : hot,new
            //page : 0,1,2,3,4...
            $url = "https://api.vc.bilibili.com/link_draw/v2/{$photo}/list?category={$category}&type={$type}&page_num={$page}&page_size=20";

            return $url;
        }

        public function run(){
            set_time_limit(0);
            for($i = 0; $i < 25; $i++){
                foreach($this->param as $key=>$val){
                    $url = $this->getUrl($val[0],$val[1],$i,'hot');
                    $this->mycurl->setUrl($url);
                    $result = $this->mycurl->exec();
                    if(!$result){
                        $this->toLog('Run--Error');
                        //var_dump($this->mycurl->error());die;
                        error_log(print_r($this->mycurl->error(),1));
                    }else{
                        $this->toLog('page'.$i.'--'.$val[0].'--'.$val[1]);
                        $this->createData(json_decode($result,true)['data']['items']);
                    }
                }

                sleep(3);
            }
        }

        protected function createData(&$data){
            $user = [];
            $photo = [];
            foreach($data as $key=>$val){
                $user[] = [
                    'uid'=>$val['user']['uid'],
                    'name'=>$val['user']['name'],
                    'cover'=>$val['user']['head_url'],
                ];
                foreach($val['item']['pictures'] as $k=>$v){
                    $photo[] = [
                        'uid'=>$val['user']['uid'],
                        'title'=>$val['item']['title'],
                        'category'=>$val['item']['category'],
                        'doc_id'=>$val['item']['doc_id'],
                        'poster_uid'=>$val['item']['poster_uid'],
                        'src'=>$v['img_src'],
                        'width'=>($v['img_width'] ? $v['img_width'] : 0),
                        'height'=>($v['img_height'] ? $v['img_height'] : 0),
                        'size'=>($v['img_size'] ? $v['img_size'] : 0),
                    ];
                }
            }

            $this->toLog(__FUNCTION__);

            //$this->toInsert('up',$user);
            $this->toInsert('photo',$photo);
        }


        protected function toInsert($table, &$data){
            //var_dump($data);die;
            $this->toLog($table.'--start');

            if($table == 'up'){
                //$this->getUserArray();
            }else if($table == 'photo'){
                //$this->getPhotoArray();
            }
            try {
                $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->db->beginTransaction();
                foreach($data as $key=>$val){
                    $addtime = time();
                    if($table == 'photo'){
                        if(!in_array($val['src'],$this->photoArray)){
                            $this->photoArray[] = $val['src'];
                            $sql = "INSERT INTO photo (`uid`,`title`,`category`,`doc_id`,`poster_uid`,`src`,`width`,`height`,`size`,`add_time`) VALUES ";
                            $sql .= "({$val['uid']}, "."'{$val['title']}'".", '{$val['category']}', {$val['doc_id']}, {$val['poster_uid']}, "."'{$val['src']}'".", {$val['width']}, {$val['height']}, {$val['size']}, {$addtime})";
                            echo $sql."\r\n";
                            if($this->db->exec($sql));
                        }
                    }else if($table == 'up'){
                        if(!in_array($val['uid'],$this->userArray)){
                            $this->userArray[] = $val['uid'];
                            $sql = "INSERT INTO up (`uid`, `cover`, `name`, `add_time`) VALUES ";
                            $sql .= "({$val['uid']}, "."'{$val['cover']}'".", '{$val['name']}'".", {$addtime})";

                            $this->db->exec($sql);
                        }
                    }
                }

                $this->db->commit();

                $this->toLog($table.'--end');

            } catch (Exception $e) {
                $this->db->rollBack();
                $this->toLog("Failed: " . $e->getMessage());
            }
        }

        protected function getUserArray(){
            $up = [];
            $res = $this->db->query("SELECT uid FROM up");
            foreach($res as $key=>$val){
                $up[] = $val['uid'];
            }
            $this->userArray = $up;

            $this->toLog(__FUNCTION__);
        }

        protected function getPhotoArray(){
            $photo = [];
            $res = $this->db->query("SELECT src FROM photo");
            foreach($res as $key=>$val){
                $photo[] = $val['src'];
            }
            $this->photoArray = $photo;

            $this->toLog(__FUNCTION__);
        }


        protected function toLog($flag)
        {
            echo '--------------------------'.$flag."\t\t".ceil(memory_get_usage()/1000000).'M'."\t\t".date('Y-m-d H:i:s').'---------------------------'."\r\n";
        }
    }


    $bili = new runExec();
    $bili->run();

