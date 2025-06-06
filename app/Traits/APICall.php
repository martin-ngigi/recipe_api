<?php

namespace App\Traits;

trait APICall
{

    public function getRequest($url, $header)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url, 
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => $header ?? array(
                'Content-Type: application/json',
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        $info = curl_getinfo($curl);
        $data = $response??$err;
        curl_close($curl);

        return [
            'api_info'=>$info,
            'data'=>json_decode($data),
            'error' => $err,
        ];
    }

    public function postRequest($url, $header, $data)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url, 
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $header ?? array(
                'Content-Type: application/json',
            ),
        ));

        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        $info = curl_getinfo($curl);
        $data = $response??$err;
        curl_close($curl);

        return [
            'api_info'=>$info,
            'data'=>json_decode($data)
        ];
    }

    public function deleteRequest($url, $header)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url, 
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "DELETE",
            CURLOPT_HTTPHEADER => $header ?? array(
                'Content-Type: application/json',
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        $info = curl_getinfo($curl);
        $data = $response??$err;
        curl_close($curl);

        return [
            'api_info'=>$info,
            'data'=>json_decode($data)
        ];
    }
}
