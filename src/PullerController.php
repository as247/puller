<?php

namespace As247\Puller;

use As247\Puller\Exceptions\InvalidTokenException;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\Request;

class PullerController
{
    function messages(Request $request, PullerManager $pullerManager, Repository $config){
        set_time_limit(3600);
        $channel=$request->input('channel');
        $token=$request->input('token');
        $isPrivate=strpos($channel, 'private-')===0;
        if(!$channel){
            return response()->json(['error'=>'channel is required'],400);
        }
        $isNewToken=false;
        if(!$isPrivate){
            if(!$token){
                $token=$pullerManager->getToken($channel);
                $isNewToken=true;
            }
        }
        $sleep=$config->get('puller.sleep',1);
        try {
            do{
                $messages = $pullerManager->pull($channel, $token);
                if($message=$messages->last()){
                    $token=$message->token;
                }
                $messages=$messages->map(function ($message){
                    return $message->payload;
                });
                if($isNewToken || !$messages->isEmpty() || connection_aborted()){
                    break;
                }
                if($sleep>0){
                    if($sleep<1){
                        usleep($sleep*1000000);
                    }else{
                        sleep(1);
                    }
                }


            }while(1);
            return response()->json(['messages' => $messages,'token'=>$token]);
        }catch (InvalidTokenException $exception){
            return response()->json(['error'=>$exception->getMessage()],401);
        }
    }
}
