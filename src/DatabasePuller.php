<?php

namespace As247\Puller;

use Illuminate\Database\ConnectionInterface;
use As247\Puller\Exceptions\InvalidTokenException;

class DatabasePuller extends Puller
{
    /**
     * The database connection instance.
     *
     * @var \Illuminate\Database\Connection
     */
    protected $database;

    /**
     * The database table that holds the jobs.
     *
     * @var string
     */
    protected $table;


    public function __construct(ConnectionInterface $database, $table)
    {
        $this->database= $database;
        $this->table = $table;
    }

    function store(Message $message){
        $id=$this->database->table($this->table)->insertGetId($message->toDatabase());
        $message->id = $id;
        return $message;
    }
    protected function touchId($id){
        $this->database->table($this->table)
            ->where('id',$id)
            ->update(['updated_at'=>$this->currentTime()]);
    }
    function fetch($channel, $token, $size)
    {
        $message=$this->database->table($this->table)
            ->select(['id','updated_at'])
            ->where('token',$token)
            ->where('channel',$channel)
            ->limit(1)
            ->first();
        if(!$message){
            throw new InvalidTokenException('Invalid token');
        }
        //Touch if too old
        if($message->updated_at<$this->currentTime()-($this->removeAfter/2)){
            $this->touchId($message->id);
        }
        return $this->database->table($this->table)
            ->where('channel',$channel)
            ->where('id','>',$message->id)
            ->where('payload','<>','')
            ->orderBy('id','asc')
            ->limit($size)
            ->get();
    }
    function purge(){
        $this->database->table($this->table)
            ->where('updated_at','<',$this->currentTime()-$this->removeAfter)
            ->delete();
    }
    function lastToken($channel){
        return $this->database->table($this->table)
            ->select('token')
            ->where('channel',$channel)
            ->orderBy('id','desc')
            ->limit(1)
            ->value('token');
    }


}
