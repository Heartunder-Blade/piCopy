<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use \QCloud_WeApp_SDK\Auth\LoginService as LoginService;
use \QCloud_WeApp_SDK\Conf as Conf;
use \QCloud_WeApp_SDK\Cos\CosAPI as Cos;
use \QCloud_WeApp_SDK\Constants as Constants;
use QCloud_WeApp_SDK\Mysql\Mysql as DB;


class Shop extends CI_Controller {
//商家打印店信息获取需要传入shopId
    public function getShop($shopId){
        try{
            $row=DB::row("shops",["*"],["id"=>$shopId]);
            $this->json([
                'code'=>0,
                'row'=>$row,
            ]);
        }catch (Expection $e){
            $this->json([
                'code'=>-1,
                'err'=>$e->getMessage(),
            ]);
        }
    }

//打印店列表获取，需要传用户的openId
    public function getShopList($openId){
        try{
            $row=DB::select("shops",["*"],["open_id"=>$openId]);
            $this->json([
                'code'=>0,
                'row'=>$row,
            ]);
        }catch (Expection $e){
            $this->json([
                'code'=>-1,
                'err'=>$e->getMessage(),
            ]);
        }
    }


//商家打印店注册    
    public function shopRegist(){
        ini_set('upload_max_filesize', '10M');
        ini_set('post_max_size', '10M');

        $file = $_FILES['file']; // 去除 field 值为 file 的文件

        // 限制文件格式，支持图片上传
        if ($file['type'] !== 'image/jpg' && $file['type'] !== 'image/jpeg' && $file['type'] !== 'image/png') {
            $this->json([
                'code' => 1,
                'data' => '不支持的上传图片类型：' . $file['type']
            ]);
            return;
        }
        
        // 限制文件大小：5M 以内
        if ($file['size'] > 5 * 1024 * 1024) {
            $this->json([
                'code' => 1,
                'data' => '上传图片过大，仅支持 5M 以内的图片上传'
            ]);
            return;
        }

        $cosClient = Cos::getInstance();
        $cosConfig = Conf::getCos();
        $bucketName = $cosConfig['fileBucket'];
        $folderName = "shopPhotos";

        try {
            /**
             * 列出 bucket 列表
             * 检查要上传的 bucket 有没有创建
             * 若没有则创建
             */
            $bucketsDetail = $cosClient->listBuckets()->toArray()['Buckets'];
            $bucketNames = [];
            foreach ($bucketsDetail as $bucket) {
                array_push($bucketNames, explode('-', $bucket['Name'])[0]);
            }

            // 若不存在 bucket 就创建 bucket
            if (count($bucketNames) === 0 || !in_array($bucketName, $bucketNames)) {
                $cosClient->createBucket([
                    'Bucket' => $bucketName,
                    'ACL' => 'public-read'
                ])->toArray();
            }

            // 上传文件
            $fileFolder = $folderName ? $folderName . '/' : '';
            $fileKey = $fileFolder . md5(mt_rand()) . '-' . $file['name'];
            $uploadStatus = $cosClient->upload(
                $bucketName,
                $fileKey,
                file_get_contents($file['tmp_name'])
            )->toArray();

        } catch (Exception $e) {
            $this->json([
                'code' =>-1,
                'error' => $e->__toString()
            ]);
        }
        $infoArry=$_POST;
        $msg=null;
        $code=0;
        try{
            DB::insert("shops",[
                "open_id"=>$infoArry['openId'],
                "name"=>$infoArry['shopName'],
                "photo"=>$uploadStatus["Location"],
                "location"=>$infoArry['address'],
                'latitude'=>$infoArry['latitude'],
                'longitude'=>$infoArry['longitude'],
                "page_price"=>$infoArry['price']
            ]);
            $this->json([
                "code"=>$code
            ]);
        }catch(Expection $e){
            $code=-1;
            $this->json([
                'code'=>$code,
                'err'=>$e->getMessage(),
            ]);
        }
    }

    public function shopUpdata(){
        $infoArry=$_POST;
        try{
            DB::update("shops",["name"=>$infoArry["shopName"],"page_price"=>$infoArry["price"]],["id"=>$infoArry['id']]);
            $this->json([
                "code"=>0,
                'form'=>$infoArry
            ]);
        }catch (Expection $e){
            $this->json([
                "code"=>-1,
                "err"=>$e->getMessage()
            ]);
        }
    }

    public function shopDelete($shopId){
        try{
            DB::delete("shops",["id"=>$shopId]);
            $this->json([
                'code'=>0
            ]);
        }catch(Expection $e){
            $this->json([
                'code'=>-1,
                'err'=>$e->getMessage()
            ]);

        }

    }

    public function getPrinter($shopId){
        try{
            $rows=DB::select("printers",["id","name","pcname"],["shop_id"=>$shopId]);
            $this->json([
                'code'=>0,
                'data'=>$rows
            ]);
        }catch(Expection $e){
            $this->json([
                code=>-1,
                err=>$e->getMessage()
            ]);
        }
    }

    public function addPrinter(){
        $messArry=$_POST;
        $shopId=$messArry['shopId'];
        $token=$messArry['token'];
        try{
            DB::update("printers",['shop_id'=>$shopId],['token'=>$token]);
            $row=DB::row("printers",['pcname'],['token'=>$token]);
            $this->json([
                'code'=>0,
            ]);

        }catch(Expection $e){
            $this->json([
                'code'=>-1,
                'err'=>$e->getMessage()
            ]);

        }
    }

    public function deletPrint($printId){
        try{
            DB::delete("printers",['id'=>$printId]);
            $this->json([
                'code'=>0,
            ]);
        }catch(Expection $e){
            $this.json([
                "code"=>-1,
                "err"=>$e->getMessage()
            ]);
        }
    }
}