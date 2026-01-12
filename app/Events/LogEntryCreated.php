<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;



class LogEntryCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;

    /**
     * Create a new event instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('logs'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'log.new';
    }

    /**
     * Get the data to broadcast.
     * Limits payload size to avoid Pusher's 10KB limit.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        $maxMessageLength = 500;
        $maxRawLength = 500;
        $maxContextLength = 2000;

        $data = $this->data;

        // Truncate message
        if (isset($data['message']) && strlen($data['message']) > $maxMessageLength) {
            $data['message'] = substr($data['message'], 0, $maxMessageLength) . '... [truncated]';
        }

        // Truncate raw
        if (isset($data['raw']) && strlen($data['raw']) > $maxRawLength) {
            $data['raw'] = substr($data['raw'], 0, $maxRawLength) . '... [truncated]';
        }

        // Handle context - convert to string and truncate if too large
        if (isset($data['context'])) {
            $contextJson = json_encode($data['context']);
            if (strlen($contextJson) > $maxContextLength) {
                $data['context'] = ['_truncated' => true, '_message' => 'Context too large to broadcast. Check server logs for full details.'];
            }
        }

        return ['data' => $data];
    }
}
