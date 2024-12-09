<?php

namespace Plugins\Order\App\Events;


use Plugins\Order\App\Models\Order;

class OrderDeletedEvent
{

    /**
     * @var Order
     */
    protected $order;

    /**
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

}
