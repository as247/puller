<?php

namespace As247\Puller;

use As247\Puller\Events\MessagesPulled;
use Illuminate\Container\Container;
use Illuminate\Support\InteractsWithTime;
use Illuminate\Support\Str;

abstract class Puller implements Contracts\Puller
{
    use InteractsWithTime;
    /**
     * The IoC container instance.
     *
     * @var \Illuminate\Container\Container
     */
    protected $container;

    /**
     * @var string The connection name for the puller.
     */
    protected $connectionName;
    /**
     * @var int The number of seconds to remove old messages
     */
    protected $removeAfter;
    /**
     * @var int The default number of messages to fetch
     */
    protected $fetchSize=10;



    abstract protected function store(Message $message);
    abstract protected function fetch($channel,$token,$size);
    abstract protected function purge();

    abstract protected function lastToken($channel);

    /**
     * @param $channel
     * @param $event
     * @param $data
     * @return Message
     */
    protected function createMessage($channel,$event,$data){
        $message = new Message();
        $message->token = $this->generateUniqueToken();
        $message->channel = $channel;
        if($event) {
            $message->payload = json_encode([$event, $data]);
        }else{
            $message->payload = '';
        }
        $message->updated_at = $this->currentTime();
        $message->created_at = $this->currentTime();
        return $message;
    }

    /**
     * @param $channel
     * @param $event
     * @param $data
     * @return mixed
     */
    public function push($channel,$event='',$data=[]){
        $this->purge();
        $message = $this->createMessage($channel, $event, $data);
        $pushed=$this->store($message);
        event(new Events\MessagePushed($pushed));
        return $pushed;

    }
    public function pull($channel,$token,$size=null){
        $size=abs(intval($size));
        if(!$size){
            $size=$this->fetchSize;
        }
        $messages=$this->fetch($channel,$token,$size);
        event(new MessagesPulled($messages,$channel,$token,$size));
        if(!$messages){
            return new MessageCollection();
        }
        return new MessageCollection($messages->mapInto(Message::class));
    }
    public function getToken($channel){
        if(!$token=$this->lastToken($channel)){
            $message=$this->push($channel);
            $token=$message->token;
        }
        return $token;
    }

    protected function generateUniqueToken(){
        return Str::random(32).Str::replace('-', '', Str::uuid());
    }

    /**
     * Get the connection name for the puller.
     *
     * @return string
     */
    public function getConnectionName()
    {
        return $this->connectionName;
    }

    /**
     * Set the connection name for the puller.
     *
     * @param  string  $name
     * @return $this
     */
    public function setConnectionName($name)
    {
        $this->connectionName = $name;

        return $this;
    }

    public function setConfig($config){
        $this->removeAfter=$config['remove_after']??60;
        $this->fetchSize=$config['fetch_size']??10;
        return $this;
    }

    /**
     * Get the container instance being used by the connection.
     *
     * @return \Illuminate\Container\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Set the IoC container instance.
     *
     * @param  \Illuminate\Container\Container  $container
     * @return void
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }
}
