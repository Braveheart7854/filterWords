<?php
/**
 * Created by PhpStorm.
 * User: tongh
 * Date: 2018/4/4
 * Time: 下午4:52
 */
include './lib/tree.php';

class start{
    public $tree;
    public $flag = 0;

    public function startServer(){
        $http = new swoole_http_server("127.0.0.1", 9501);
        
        $http->on('request', function ($request, $response) {
            if ($this->flag == 0){
                $tree = new TrieTree();

                $myfile = fopen("./lib/words/words.txt", "r") or die("Unable to open file!");
                while(!feof($myfile)) {
                    $content = fgets($myfile);
                    $tree->insert($content);
                }
                fclose($myfile);

                $this->tree = $tree;
                $this->flag = 1;
            }

            $uri = $request->server['request_uri'];

            if ($uri != '/favicon.ico'){
                $word = $request->get['q'];
//                $res = $this->tree->find($word);
                $res = $this->tree->contain($word);
                if ($res == false){
                    $msg = json_encode(['code'=>10000,'filter'=>'','result'=>$word]);
                }else{
                    foreach ($res as $item){
                        $word = str_replace($item,str_pad('', mb_strlen($item),'*') , $word);
                    }
                    $msg = json_encode(['code'=>10000,'filter'=>$res,'result'=>$word]);
                }
                $response->end($msg);
            }
        });
        $http->start();
    }
}

$app = new start();
$app->startServer();
