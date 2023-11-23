<?php

namespace As247\Puller;

use Illuminate\Broadcasting\Broadcasters\Broadcaster;
use Illuminate\Broadcasting\Broadcasters\UsePusherChannelConventions;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class PullerBroadcaster  extends Broadcaster
{
    use UsePusherChannelConventions;
    protected $puller;
    public function __construct(PullerManager $puller)
    {
        $this->puller = $puller;
    }

    public function auth($request)
    {
        $channelName = $this->normalizeChannelName($request->channel_name);

        if (empty($request->channel_name) ||
            ($this->isGuardedChannel($request->channel_name) &&
                ! $this->retrieveUser($request, $channelName))) {
            throw new AccessDeniedHttpException;
        }

        return parent::verifyUserCanAccessChannel(
            $request, $channelName
        );
    }

    public function validAuthenticationResponse($request, $result)
    {
        $token = $this->puller->getToken($request->channel_name);
        event(new Events\ChannelAuthenticated($request->channel_name,$token));
        return [
            'token'=>$token,
        ];

    }

    public function broadcast(array $channels, $event, array $payload = [])
    {
        foreach ($channels as $channel) {
            $this->puller->push($channel, $event, $payload);
        }
    }
}
