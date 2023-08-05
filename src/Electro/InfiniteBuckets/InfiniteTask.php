<?php

namespace Electro\InfiniteBuckets;

use Electro\InfiniteBuckets\InfiniteBuckets;

use pocketmine\scheduler\Task;

class InfiniteTask extends Task{

    private $plugin;
    private $player;
    private $item;

    public function __construct(InfiniteBuckets $plugin, $player, $item){
        $this->plugin = $plugin;
        $this->player = $player;
        $this->item = $item;

    }

    public function onRun() : void
    {
        $this->player->getInventory()->setItemInHand($this->item);

    }
}
