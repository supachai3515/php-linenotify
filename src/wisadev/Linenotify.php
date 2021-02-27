<?php

namespace Wisadev;

use Exception;

class LineNotify
{
    protected $clientId;
    protected $clientSecret;

    public function __construct($clientId, $clientSecret)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    public function urlOauth($redirectUri, $refId)
    {
        try {
            $url = 'https://notify-bot.line.me/oauth/authorize?';
            $url = $url . 'response_type=code';
            $url = $url . '&client_id=' . $this->clientId;
            $url = $url . '&redirect_uri=' . $redirectUri; //ถ้า login แล้ว เลือกกลุ่มหรือตัวเอง ให้กลับมาหน้านี้
            $url = $url . '&scope=notify';
            $url = $url . '&state=' .  $refId;
            return $url;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function callbackGetToken($redirectUri, $code, $state)
    {
        try {
            $msg = null;
            $result_ = null;
            if (isset($code) && isset($state)) {
                $postData = 'grant_type=authorization_code';
                $postData = $postData . '&code=' . $code;
                $postData = $postData . '&redirect_uri=' . $redirectUri;
                $postData = $postData . '&client_id=' . $this->clientId;
                $postData = $postData . '&client_secret=' . $this->clientSecret;

                $chOne = curl_init();
                curl_setopt($chOne, CURLOPT_URL, "https://notify-bot.line.me/oauth/token");
                // SSL USE 
                curl_setopt($chOne, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($chOne, CURLOPT_SSL_VERIFYPEER, 0);
                //POST 
                curl_setopt($chOne, CURLOPT_POST, 1);
                // Message 
                curl_setopt($chOne, CURLOPT_POSTFIELDS, $postData);
                curl_setopt($chOne, CURLOPT_FOLLOWLOCATION, 1);
                //RETURN 
                curl_setopt($chOne, CURLOPT_RETURNTRANSFER, 1);
                $result = curl_exec($chOne);
                //Check error 
                if (curl_error($chOne)) {
                    $msg =  'error:' . curl_error($chOne);
                } else {

                    $result_ = json_decode($result, true);
                }
                //Close connect 
                curl_close($chOne);
                if (isset($msg)) {
                    throw new Exception($msg);
                }
                return  $result_;
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function sendMsg($token, $message, $imageUrl = null)
    {
        try {
            $result_ = null;
            if (isset($token) && !empty($token) &&  isset($message) && !empty($message)) {
                $chOne = curl_init();
                curl_setopt($chOne, CURLOPT_URL, "https://notify-api.line.me/api/notify");
                // SSL USE 
                curl_setopt($chOne, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($chOne, CURLOPT_SSL_VERIFYPEER, 0);
                //POST 
                curl_setopt($chOne, CURLOPT_POST, 1);
                // Message 
                curl_setopt($chOne, CURLOPT_POSTFIELDS, "message=" . $message);
                //ถ้าต้องการใส่รุป ให้ใส่ 2 parameter imageThumbnail และ imageFullsize 
                if (isset($imageUrl)) {
                    curl_setopt($chOne, CURLOPT_POSTFIELDS, "message=hi&imageThumbnail=" . $imageUrl . "&imageFullsize=" . $imageUrl . "");
                }
                // follow redirects
                curl_setopt($chOne, CURLOPT_FOLLOWLOCATION, 1);
                //ADD header array 
                $headers = array('Content-type: application/x-www-form-urlencoded', 'Authorization: Bearer ' .  $token,);
                curl_setopt($chOne, CURLOPT_HTTPHEADER, $headers);
                //RETURN 
                curl_setopt($chOne, CURLOPT_RETURNTRANSFER, 1);
                $result = curl_exec($chOne);
                //Check error 
                if (curl_error($chOne)) {
                    //echo 'error:' . curl_error($chOne);
                    throw new Exception(curl_error($chOne));
                } else {
                    $result_ = json_decode($result, true);
                    //echo "status : ".$result_['status']; echo "message : ". $result_['message']; 
                    if ($result_["status"] != 200) {
                        throw new Exception(curl_error($result_["message"]));
                    }
                }
                //Close connect 
                curl_close($chOne);
            } else {
                throw new Exception("error info");
            }
            return  $result_;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
