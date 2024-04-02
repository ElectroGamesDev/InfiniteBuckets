<?php

namespace Electro\InfiniteBuckets;

use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\Block\BlockTypeIds;
use pocketmine\plugin\PluginBase;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\event\Listener;

class InfiniteBuckets extends PluginBase implements Listener{

    public array $types = ["lava", "water"];

    public function onEnable() : void
    {
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{
        switch($command->getName()) {
            case "buckets":
                if (!$sender->hasPermission("infinitebuckets.cmd")){
                    $sender->sendMessage("§cYou do not have permissions to use this command");
                    return true;
                }
                if (!isset($args[0])){
                    $sender->sendMessage("§l§cUsage: §r§a/buckets <give/info>");
                    return true;
                }
                switch (strtolower($args[0])) {
                    case "give":
                        if (!isset($args[1])) {
                            $sender->sendMessage("§l§cUsage: §r§a/buckets give <Player> <Water/Lava>");
                            return true;
                        }
                        if (!$this->getServer()->getPlayerExact($args[1]) instanceof Player) {
                            $sender->sendMessage("§l§cERROR: §r§aYou have entered an invalid Player Username.");
                            return true;
                        }
                        if (!isset($args[2]) || !in_array($args[2], $this->types)) {
                            $sender->sendMessage("§l§cERROR: §r§aYou have entered an invalid bucket type.");
                            return true;
                        }

                        $player = $this->getServer()->getPlayerExact($args[1]);
                        $player->sendMessage("§aYou have given " . $player->getname() . " a " . $args[2] . " Bucket!");
                        if ($args[2] === "water") {
                            $item = VanillaItems::WATER_BUCKET();
                            $item->setCustomName("§r§cInfinite Water Bucket");
                            $item->setLore(["§r§7Right Click/Tap To Place Water"]);
                            $item->getNamedTag()->setString("Creator", $sender->getName());
                            $item->getNamedTag()->setString("Type", "Water");
                            $item->getNamedTag()->setString("InfiniteBuckets", "InfiniteBuckets");
                            $player->getInventory()->addItem($item);
                        }
                        else{
                            $item = VanillaItems::LAVA_BUCKET();
                            $item->setCustomName("§r§cInfinite Lava Bucket");
                            $item->setLore(["§r§7Right Click/Tap To Place Lava"]);
                            $item->getNamedTag()->setString("Creator", $sender->getName());
                            $item->getNamedTag()->setString("Type", "Lava");
                            $item->getNamedTag()->setString("InfiniteBuckets", "InfiniteBuckets");
                            $player->getInventory()->addItem($item);
                        }
                        break;
                    case "info":
                        if (!$sender instanceof Player){
                            $sender->sendMessage("§cYou must be in-game to run this command");
                            return true;
                        }
                        $item = $sender->getInventory()->getItemInHand();
                        if (!$item->getNamedTag()->getTag("InfiniteBuckets")) {
                            $sender->sendMessage("§l§cError: §r§aYou must be holding an Infinite Bucket");
                            return true;
                        }
                        $sender->sendMessage("§aBucket Created By: §b" . $item->getNamedTag()->getString("Creator") . "\n§aBucket Creation Date/Time: §b" . date("Y-m-d H:i"));
                        break;
                    default:
                        $sender->sendMessage("§l§cUsage: §r§a/buckets <give/info>");
                        return true;
                }
        }
        return true;
    }

    public function onPlace(PlayerInteractEvent $event)
    {
        $player = $event->getPlayer();
        $item = $event->getItem();
        $block = $event->getBlock();
        if ($event->getAction() !== PlayerInteractEvent::RIGHT_CLICK_BLOCK || !$item->getNamedTag()->getTag("InfiniteBuckets") || $block->getTypeId() !== BlockTypeIds::ITEM_FRAME || $block->getTypeId() !== BlockTypeIds::GLOWING_ITEM_FRAME){
            return true;
        }
        if ($item->getNamedTag()->getString("Type") === "Water") {
            if ($this->getConfig()->get("Water_Bucket_Requires_Perm") === true && !$player->hasPermission("Water_Bucket_Requires_Perm")) {
                $player->sendMessage("§cYou do not have permissions to use this item");
                $event->cancel();
                return true;
            }
        }

        if ($item->getNamedTag()->getString("Type") === "Lava") {
            if ($this->getConfig()->get("Lava_Bucket_Requires_Perm") === true && !$player->hasPermission("Lava_Bucket_Requires_Perm")) {
                $player->sendMessage("§cYou do not have permissions to use this item");
                $event->cancel();
                return true;
            }
        }

        $this->getScheduler()->scheduleDelayedTask(new InfiniteTask($this, $player, $item), 1);
    }
}
