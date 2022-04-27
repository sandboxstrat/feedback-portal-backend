<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Log;

class Auth0ManagementApiController extends Controller
{

    private $accessToken;

    public function __construct(){
        try{

            //Retrieves access token for Auth0 Management API
            $clientId = env('AUTH0_CLIENT_ID');
            $clientSecret = env('AUTH0_CLIENT_SECRET');
            $clientAudience = env('AUTH0_MANAGEMENT_AUDIENCE');

            $curl = curl_init();

            curl_setopt_array($curl, [
            CURLOPT_URL => env('AUTH0_MANAGEMENT_TOKEN_API'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "grant_type=client_credentials&client_id=$clientId&client_secret=$clientSecret&audience=$clientAudience",
            CURLOPT_HTTPHEADER => [
                "content-type: application/x-www-form-urlencoded"
            ],
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                Log::info("cURL Error #:" . $err);
            } else {
                $decodedResponse = json_decode($response,true);
                $accessToken = $decodedResponse['access_token'];
                $this->curl = curl_init();

                curl_setopt_array($this->curl, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_HTTPHEADER => [
                        "content-type: application/json",
                        "authorization: Bearer $accessToken"
                    ],
                    ]);

            }

            
        }catch(Throwable $t){
            Log::info($t);
        }
        
    }

    private function processCurl($errorMessage){

        $response = curl_exec($this->curl);
        $err = curl_error($this->curl);

        if ($err) {
            Log::info("cURL Error #:" . $err);
            return reponse($errorMessage,400);
        } else {
            return response($response,200);
        }
    }

    public function getAllUsers()
    {
        curl_setopt($this->curl, CURLOPT_URL, env('AUTH0_MANAGEMENT_USERS_API'));
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "GET");

        $this->processCurl("Error retrieving users");
    }

    public function getUser($userId){
        curl_setopt($this->curl, CURLOPT_URL, env('AUTH0_MANAGEMENT_USERS_API')."/".$userId);
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "GET");
        
        $this->processCurl("Error retrieving user");

    }

    public function createUser($userInfo){

        curl_setopt($this->curl, CURLOPT_URL, env('AUTH0_MANAGEMENT_USERS_API'));
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "POST");
        
        $curlBody = array(
            'email'=>$userInfo['email'],
            'phone_number'=>$userInfo['phoneNumber'],
            'email_verified'=>false,
            'phone_verified'=>false,
            'given_name'=>$userInfo['firstName'],
            'family_name'=>$userInfo['lastName'],
            'name'=>$userInfo['firstName'].' '.$userInfo['lastName'],
            'nickname'=>$userInfo['firstName'],
            'connection'=>'Initial-Connection',
            'verify_email'=>true,
            'app_metadata'=>array(
                'user_type'=>$userInfo['userType']
            ),
            'blocked'=>false,
        );

        curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($curlBody));

        $this->processCurl("Error creating user");
    }

    public function updateUser($userId,$userInfo){

        curl_setopt($this->curl, CURLOPT_URL, env('AUTH0_MANAGEMENT_USERS_API'));
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "PATCH");
        
        $curlBody = array(
            'email'=>$userInfo['email'],
            'phone_number'=>$userInfo['phoneNumber'],
            'email_verified'=>false,
            'phone_verified'=>false,
            'given_name'=>$userInfo['firstName'],
            'family_name'=>$userInfo['lastName'],
            'name'=>$userInfo['firstName'].' '.$userInfo['lastName'],
            'nickname'=>$userInfo['firstName'],
            'connection'=>'Initial-Connection',
            'verify_email'=>true,
            'app_metadata'=>array(
                'user_type'=>$userInfo['userType']
            ),
            'blocked'=>$userInfo['blocked'],
        );

        curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($curlBody));

        $this->processCurl("Error updating user");
        
    }

    function __destruct(){
        curl_close($this->curl);
    }

}