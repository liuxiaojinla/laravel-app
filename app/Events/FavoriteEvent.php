<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FavoriteEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var string
     */
    protected $topicType;

    /**
     * @var int
     */
    protected $topicId;

    /**
     * @var bool
     */
    protected $isFavorite;

    /**
     * Create a new event instance.
     * @param string $topicType
     * @param int $topicId
     * @param bool $isFavorite
     */
    public function __construct($topicType, $topicId, $isFavorite)
    {
        $this->topicType = $topicType;
        $this->topicId = $topicId;
        $this->isFavorite = $isFavorite;
    }

    /**
     * @return string
     */
    public function getTopicType()
    {
        return $this->topicType;
    }

    /**
     * @return int
     */
    public function getTopicId()
    {
        return $this->topicId;
    }

    /**
     * @return bool
     */
    public function isFavorite()
    {
        return $this->isFavorite;
    }

    /**
     * @return string
     */
    public function getTopicClass()
    {
        return Morph::getType($this->topicType);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
