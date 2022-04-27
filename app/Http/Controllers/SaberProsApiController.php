<?php

namespace App\Http\Controllers;

use App\Models\ProsState;
use App\Models\ProsCookieManagement;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Log;

class SaberProsApiController extends Controller
{

    private $accessToken;
    private $refreshCookie;

    private function start($code,$type='authorization_code')
    {
        try{

            //Retrieves access token 
            $clientId = env('SABER_PROS_CLIENT_ID');
            $clientSecret = env('SABER_PROS_CLIENT_SECRET');
            $redirectUri = env('SABER_PROS_REDIRECT_URI');

            $codeType=($type==='authorization_code')?'code':'refresh_token';
            $postfield = "grant_type=$type&$codeType=$code&client_id=$clientId&client_secret=$clientSecret&redirect_uri=$redirectUri";
            
            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => env('SABER_PROS_TOKEN_ENDPOINT'),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $postfield,
                CURLOPT_HTTPHEADER => [
                    "content-type: application/x-www-form-urlencoded"
                ],
            ]);

            $response = curl_exec($curl);
            $decodedResponse = json_decode($response,true);
            $error = !empty($decodedResponse['error'])?$decodedResponse:null;

            curl_close($curl);

            if ($error) {
                Log::info("cURL Error #:" . $error['error_description']);
            } else {
                $decodedResponse = json_decode($response,true);
                if(!empty($decodedResponse))
                {
                    $accessToken = $decodedResponse['access_token'];
                    $this->accessToken = $decodedResponse['access_token'];

                    //decodes ID Token
                    $this->idToken = $decodedResponse['id_token'];
                    $this->decodedIdToken = json_decode(base64_decode(str_replace('_', '/', str_replace('-','+',explode('.', $this->idToken)[1]))),true);
                    $this->sub = $this->decodedIdToken['sub'];
                    
                    //store refresh token in database
                    $refreshToken = $decodedResponse['refresh_token'];
                    $cookieRequest = new Request();
                    $cookieRequest->replace(['refresh_token'=>$refreshToken]);
                    $this->refreshCookie = json_decode($this->createRefreshCookie($cookieRequest),true);

                    $this->refreshCookieKey = bin2hex(random_bytes(20));
                    $refreshCookieHash = hash_hmac('sha256',$this->refreshCookie['id'],$this->refreshCookieKey);

                    $updateCookieRequest = new Request();
                    $updateCookieRequest->replace(['hash'=>$refreshCookieHash]);
                    
                    $this->addHashToRefreshCookie($this->refreshCookie['id'], $updateCookieRequest);

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
            }

            
        }catch(Throwable $t){
            Log::info($t);
        }
        
    }

    public function getUser($authorizationCode=null,$type='authorization_code')
    {
        $this->start($authorizationCode,$type);
        if(!empty($this->accessToken))
        {
            curl_setopt($this->curl, CURLOPT_URL, env('SABER_PROS_USERINFO_ENDPOINT'));
            curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "GET");

            $response = curl_exec($this->curl);
            $decodedResponse = json_decode($response,true);

            $error = !empty($decodedResponse['error'])?$decodedResponse:null;

            $returnedUserInfo['refresh_cookie'] = $this->refreshCookie['id'];
            $returnedUserInfo['refresh_cookie_key'] = $this->refreshCookieKey;
            $returnedUserInfo['userid']=$this->sub;
            $returnedUserInfo['username']=$decodedResponse['username'];

            if ($error) {
                Log::info("cURL Error #:" . $err);
                return response()->json(['error_message'=>"Error retrieving user"],400);
            } else {
                return response()->json($returnedUserInfo,200);
            }
        }else{
            return response()->json(['error_message'=>"Invalid Login or Cookie"],400);
        }
    }

    private function getRefreshTokenByCookie($auth,$key)
    {
        $refreshInfo = ProsCookieManagement::find($auth);
        $clientHmac = hash_hmac('sha256',$auth,$key);
        if(!empty($refreshInfo) && $clientHmac === $refreshInfo['hash']){
            return $refreshInfo;
        }else{
            $this->deleteCookie($auth);
            return null;
        }
    }

    public function getUserByCookie(Request $request)
    {
        $bodyContent = json_decode($request->getContent(),true);
        $refreshToken = json_decode($this->getRefreshTokenByCookie($bodyContent['auth'],$bodyContent['key']),true);
        if(!empty($refreshToken)){
            $this->deleteCookie($bodyContent['auth']);
            return $this->getUser($refreshToken['refresh_token'],'refresh_token');
        }else{
            return response()->json(['error_message'=>"Invalid Login or Cookie"],400);
        }
    }

    public function getLoginState($id)
    {
        $loginState = json_decode(ProsState::find($id),true);
        if(!empty($loginState)){

            $game = json_decode(Game::find($loginState['game_id']),true);

            $response = ['game_page'=>$game['url'],'option'=>$loginState['option']];
            return response()->json($response,200);

        }else{
            return response()->json(['error_message'=>"Invalid Login or Cookie"],400);
        }
    }

    public function createLoginState(Request $request)
    {
        $cookie = ProsState::create($request->all());

        return response()->json($cookie, 201);
    }

    public function deleteLoginState($id)
    {
        ProsState::findOrFail($id)->delete();
        return response('Deleted Successfully', 200);
    }

    private function createRefreshCookie(Request $request)
    {
        $cookie = ProsCookieManagement::create($request->all());

        return $cookie;
    }

    private function addHashToRefreshCookie($id, Request $request)
    {
        $option = ProsCookieManagement::findOrFail($id);
        $option->update($request->all());

        return response()->json($option, 200);
    }

    public function deleteCookie($id)
    {
        ProsCookieManagement::findOrFail($id)->delete();
        return response('Deleted Successfully', 200);
    }

    public function logOut(Request $request)
    {
        $bodyContent = json_decode($request->getContent(),true);
        return $this->deleteCookie($bodyContent['auth']);
    }

    function __destruct(){
        if(!empty($this->curl))
        {
            curl_close($this->curl);
        }
        
    }

}