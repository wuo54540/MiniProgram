<?php

namespace app\logic\controller;

// use think\Db;
// use think\Session;
// use think\Cookie;
use think\Controller;
// use app\logic\logic\UploadLogic;

class Ueditor extends Controller{
	
	function __construct()
    {
        parent::__construct();
        
        date_default_timezone_set("Asia/Shanghai");
        
        // $this->savePath = I('savepath','temp').'/';
        $this->savePath = '';
        error_reporting(E_ERROR | E_WARNING);
        
        header("Content-Type: text/html; charset=utf-8");
    }
	
	public function index(){
		
        $cfg = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents(get_env('ROOT_PATH')."public/static/plugins/Ueditor/config.json")), true);
        $action = $_GET['action'];
        
        switch ($action) {
            case 'config':
                $result =  json_encode($cfg);
                break;
            /* 上传图片 */
            case 'uploadimage':
		        $fieldName = $cfg['imageFieldName'];
		        $result = $this->upFile($fieldName);
		        break;
            /* 上传涂鸦 */
            case 'uploadscrawl':
		        $config = array(
		            "pathFormat" => $cfg['scrawlPathFormat'],
		            "maxSize" => $cfg['scrawlMaxSize'],
		            "allowFiles" => $cfg['scrawlAllowFiles'],
		            "oriName" => "scrawl.png"
		        );
		        $fieldName = $cfg['scrawlFieldName'];
		        $base64 = "base64";
		        $result = $this->upBase64($config,$fieldName);
		        break;
            /* 上传视频 */
            case 'uploadvideo':
		        $fieldName = $cfg['videoFieldName'];
		        $result = $this->upFile($fieldName);
		        break;
            /* 上传文件 */
            case 'uploadfile':
		        $fieldName = $cfg['fileFieldName'];
		        $result = $this->upFile($fieldName);
                break;
            /* 列出图片 */
            case 'listimage':
			    $allowFiles = $cfg['imageManagerAllowFiles'];
			    $listSize = $cfg['imageManagerListSize'];
			    $path = $cfg['imageManagerListPath'];
			    $get =$_GET;
			    $result =$this->fileList($allowFiles,$listSize,$get);
                break;
            /* 列出文件 */
            case 'listfile':
			    $allowFiles = $cfg['fileManagerAllowFiles'];
			    $listSize = $cfg['fileManagerListSize'];
			    $path = $cfg['fileManagerListPath'];
			    $get = $_GET;
			    $result = $this->fileList($allowFiles,$listSize,$get);
                break;
            /* 抓取远程文件 */
            case 'catchimage':
		    	$config = array(
			        "pathFormat" => $cfg['catcherPathFormat'],
			        "maxSize" => $cfg['catcherMaxSize'],
			        "allowFiles" => $cfg['catcherAllowFiles'],
			        "oriName" => "remote.png"
			    );
			    $fieldName = $cfg['catcherFieldName'];
			    /* 抓取远程图片 */
			    $list = array();
			    isset($_POST[$fieldName]) ? $source = $_POST[$fieldName] : $source = $_GET[$fieldName];
				
			    foreach($source as $imgUrl){
			        $info = json_decode($this->saveRemote($config,$imgUrl),true);
			        array_push($list, array(
			            "state" => $info["state"],
			            "url" => $info["url"],
			            "size" => $info["size"],
			            "title" => htmlspecialchars($info["title"]),
			            "original" => htmlspecialchars($info["original"]),
			            "source" => htmlspecialchars($imgUrl)
			        ));
			    }

			    $result = json_encode(array(
			        'state' => count($list) ? 'SUCCESS':'ERROR',
			        'list' => $list
			    ));
                break;
            default:
                $result = json_encode(array(
                    'state' => '请求地址出错'
                ));
                break;
        }

        /* 输出结果 */
        if(isset($_GET["callback"])){
            if(preg_match("/^[\w_]+$/", $_GET["callback"])){
                echo htmlspecialchars($_GET["callback"]).'('.$result.')';
            }else{
                echo json_encode(array(
                    'state' => 'callback参数不合法'
                ));
            }
        }else{
            echo $result;
        }
	}

	//上传文件
	private function upFile($fieldName){
		$file = request()->file($fieldName);
		
		$result = $this->validate(
				['file2' => $file],
				['file2'=>'image','file2'=>'fileSize:50000000'],
				['file2.image' => '上传文件必须为图片','file2.fileSize' => '上传文件过大']
		);
		
		if (true !== $result || empty($file)) {
			$state = "ERROR" . $result;
		}else{
			// 移动到目录下
			$this->savePath = $this->savePath.date('Y').'/'.date('m-d').'/';
			// 使用自定义的文件保存规则
			$info = $file->rule(function ($file) {
				return  md5(mt_rand());
			})->move('uploads/'.$this->savePath);
		}
		
		if($info){
			// $uploadLogic = new UploadLogic('file','article');
			// $uploadLogic->insertData($info,'uploads/'.$this->savePath);
			$data = array(
				'state' => 'SUCCESS',
				'url' => '/uploads/'.$this->savePath.$info->getSaveName(),
				'title' => $info->getFilename(),
				'original' => $info->getFilename(),
				'type' => '.' . $info->getExtension(),
				'size' => $info->getSize(),
			);
			//图片加水印
			if($this->savePath=='goods/'){
				$image = new \Think\Image();
				$water = tpCache('water');
				$imgresource = ".".$data['url'];
				$image->open($imgresource);
				if($water['is_mark']==1 && $image->width()>$water['mark_width'] && $image->height()>$water['mark_height']){
					if($water['mark_type'] == 'text'){
						$image->text($water['mark_txt'],'./hgzb.ttf',20,'#000000',9)->save($imgresource);
					}else{
						$image->water(".".$water['mark_img'],9,$water['mark_degree'])->save($imgresource);
					}
				}
			}
		}else{
			// $data = array('state' => $state.$info->getError());
			$data = array('status'=>'fail');
		}
		return json_encode($data);
	}

	/*
	 * 处理base64编码的图片上传
	*/
	private function upBase64($config,$fieldName){
	    $base64Data = $_POST[$fieldName];
	    $img = base64_decode($base64Data);

	    $dirname = './uploads/scrawl/';
	    $file['filesize'] = strlen($img);
	    $file['oriName'] = $config['oriName'];
	    $file['ext'] = strtolower(strrchr($config['oriName'],'.'));
	    $file['name'] = uniqid().$file['ext'];
	    $file['fullName'] = $dirname.$file['name'];
	    $fullName = $file['fullName'];

 	    //检查文件大小是否超出限制
	    if($file['filesize'] >= ($config["maxSize"])){
  		    $data=array(
			    'state' => '文件大小超出网站限制',
		    );
		    return json_encode($data);
	    }

	    //创建目录失败
	    if(!file_exists($dirname) && !mkdir($dirname,0777,true)){
	        $data=array(
			    'state' => '目录创建失败',
		    );
		    return json_encode($data);
	    }else if(!is_writeable($dirname)){
	        $data=array(
			    'state' => '目录没有写权限',
		    );
		    return json_encode($data);
	    }

	    //移动文件
	    if(!(file_put_contents($fullName, $img) && file_exists($fullName))){ //移动失败
            $data=array(
		        'state' => '写入文件内容错误',
		    );
	    }else{ //移动成功	       
	        $data=array(
			    'state' => 'SUCCESS',
			    'url' => substr($file['fullName'],1),
			    'title' => $file['name'],
			    'original' => $file['oriName'],
			    'type' => $file['ext'],
			    'size' => $file['filesize'],
		    );
	    }
		
	    return json_encode($data);
	}

	//列出图片
	private function fileList($allowFiles,$listSize,$get){
		$dirname = './uploads/';
		$allowFiles = substr(str_replace(".","|",join("",$allowFiles)),1);
		/* 获取参数 */
		$size = isset($get['size']) ? htmlspecialchars($get['size']) : $listSize;
		$start = isset($get['start']) ? htmlspecialchars($get['start']) : 0;
		$end = $start + $size;
		/* 获取文件列表 */
		$path = $dirname;
		$files = $this->getFiles($path,$allowFiles);
		if(!count($files)){
		    return json_encode(array(
		        "state" => "no match file",
		        "list" => array(),
		        "start" => $start,
		        "total" => count($files)
		    ));
		}
		/* 获取指定范围的列表 */
		$len = count($files);
		for($i = min($end, $len) - 1, $list = array(); $i < $len && $i >= 0 && $i >= $start; $i--){
		    $list[] = $files[$i];
		}

		/* 返回数据 */
		$result = json_encode(array(
		    "state" => "SUCCESS",
		    "list" => $list,
		    "start" => $start,
		    "total" => count($files)
		));

		return $result;
	}
	
	/*
	 * 遍历获取目录下的指定类型的文件
	 * @return array
	*/
    private function getFiles($path,$allowFiles,&$files = array()){
	    if(!is_dir($path)) return null;
	    if(substr($path,strlen($path)-1) != '/') $path .= '/';
	    $handle = opendir($path);
			
	    while(false !== ($file = readdir($handle))){
	        if($file != '.' && $file != '..'){
	            $path2 = $path.$file;
	            if(is_dir($path2)){
	                $this->getFiles($path2,$allowFiles,$files);
	            }else{
		            if(preg_match("/\.(".$allowFiles.")$/i",$file)){
		                $files[] = array(
		                    'url' => substr($path2,1),
		                    'mtime' => filemtime($path2)
		                );
		            }
	            }
	        }
	    }		
	    return $files;
    }
	
}

?>