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

    public function __construct()
    {
        $tree = new TrieTree();
        $this->tree = $tree;
    }

    public function startServer(){
        $http = new swoole_http_server("127.0.0.1", 9501);

        $http->on('WorkerStart',[$this,'workStart']);
        $http->on('request',[$this,'request']);

        $http->start();
    }

    public function workStart($server){
        $myfile = fopen("./lib/words/words.txt", "r") or die("Unable to open file!");
        while(!feof($myfile)) {
            $content = trim(fgets($myfile));
            $this->tree->insert($content);
        }
        fclose($myfile);
    }

    public function request($request, $response){
        $uri = $request->server['request_uri'];

        if ($uri != '/favicon.ico'){
            $pword = $request->post['q'] ?? '';
            $gword = $request->get['q'] ?? '';
            $word = $pword ? $pword : $gword;
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

    }
}

$app = new start();
$app->startServer();
