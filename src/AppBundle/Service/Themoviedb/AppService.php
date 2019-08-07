<?php
/**
 * Created by PhpStorm.
 * User: CodingByJerez
 * Url: http://coding.by.jerez.me
 * Github: https://github.com/CodingByJerez
 * Date: 27/07/2019
 * Time: 09:39
 */

namespace AppBundle\Service\Themoviedb;


abstract class AppService
{

    protected function curlConnect($url){
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "{}",
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if($err)
            throw new \Exception("error curl => {$err}");

        return json_decode($response);
    }

    protected function recoverImgBase64($path){
        if(!empty($path)){
            $image = file_get_contents("https://image.tmdb.org/t/p/w200" . $path);
            if ($image !== false){
                return 'data:image/jpg;base64,'.base64_encode($image);
            }
        }

        return null;
    }

}