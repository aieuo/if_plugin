<?php

namespace aieuo\ip;

use pocketmine\event\Event;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\inventory\PlayerInventory;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\network\mcpe\protocol\InteractPacket;
use pocketmine\event\server\CommandEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\player\PlayerToggleFlightEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\Server;
use pocketmine\Player;

use aieuo\ip\processes\SetSitting;
use aieuo\ip\form\Form;
use aieuo\ip\utils\Language;

class EventListener implements Listener {

    /** @var IFPlugin */
    private $owner;
    private $touch;

    public function __construct($owner) {
        $this->owner = $owner;
    }

    private function getOwner(): IFPlugin {
        return $this->owner;
    }

    public function command(CommandEvent $event) {
        $sender = $event->getSender();
        if (!($sender instanceof Player)) return;

        if ($event->isCancelled()) return;

        $cmd = $event->getCommand();
        $manager = $this->getOwner()->getCommandManager();
        $original = $manager->getOriginCommand($cmd);
        if ($manager->exists($original)) {
            $data = $manager->get($cmd);
            if ($data === null) {
                $data = $manager->get($original);
            }
            if ($data["permission"] == "ifplugin.customcommand.op" and !$sender->isOp()) return;
            $manager->executeIfMatchCondition(
                $sender,
                $data["if"],
                $data["match"],
                $data["else"],
                [
                    "player" => $sender,
                    "command" => $cmd,
                    "event" => $event
                ]
            );
        }
    }

    public function commandProcess(PlayerCommandPreprocessEvent $event) {
        if ($event->getMessage()[0] !== "/") return;
        $this->onEvent($event, "PlayerCommandPreprocessEvent");
    }

    public function chat(PlayerChatEvent $event) {
        $this->onEvent($event, "PlayerChatEvent");
    }

    public function join(PlayerJoinEvent $event) {
        Session::createSession($event->getPlayer());

        $this->onEvent($event, "PlayerJoinEvent");
    }

    public function craft(CraftItemEvent $event) {
        $this->onEvent($event, "CraftItemEvent");
    }

    public function quit(PlayerQuitEvent $event) {
        $this->onEvent($event, "PlayerQuitEvent");
    }

    public function toggleFlight(PlayerToggleFlightEvent $event) {
        $this->onEvent($event, "PlayerToggleFlightEvent");
    }

    public function toggleSneak(PlayerToggleSneakEvent $event) {
        $this->onEvent($event, "PlayerToggleSneakEvent");
    }

    public function blockBreak(BlockBreakEvent $event) {
        $this->onEvent($event, "BlockBreakEvent");
    }

    public function blockPlace(BlockPlaceEvent $event) {
        $this->onEvent($event, "BlockPlaceEvent");
    }

    public function entityDamage(EntityDamageEvent $event) {
        $this->onEvent($event, "EntityDamageEvent");
    }

    public function entityDamageByEntity(EntityDamageByEntityEvent $event) {
        $this->onEvent($event, "EntityAttackEvent");
    }

    public function playerDropItemEvent(PlayerDropItemEvent $event) {
        $this->onEvent($event, "PlayerDropItemEvent");
    }

    public function inventoryPickupItem(InventoryPickupItemEvent $event) {
        $this->onEvent($event, "InventoryPickupItemEvent");
    }

    public function playerDeath(PlayerDeathEvent $event) {
        $this->onEvent($event, "PlayerDeathEvent");

        $player = $event->getEntity();
        if ($player instanceof Player) SetSitting::leave($player);
    }

    public function entityLevelChange(EntityLevelChangeEvent $event) {
        $this->onEvent($event, "EntityLevelChangeEvent");

        $player = $event->getEntity();
        if ($player instanceof Player) SetSitting::leave($player);
    }

    public function onEvent(Event $event, string $eventname) {
        switch ($eventname) {
            case 'CraftItemEvent':
            case 'PlayerInteractEvent':
            case 'PlayerChatEvent':
            case 'PlayerCommandPreprocessEvent':
            case 'PlayerJoinEvent':
            case 'PlayerQuitEvent':
            case 'PlayerToggleFlightEvent':
            case 'PlayerToggleSneakEvent':
            case 'PlayerDropItemEvent':
            case 'BlockBreakEvent':
            case 'BlockPlaceEvent':
            case "PlayerDeathEvent":
                $player = $event->getPlayer();
                break;
            case 'EntityDamageEvent':
            case 'EntityLevelChangeEvent':
                $player = $event->getEntity();
                if (!($player instanceof Player)) return;
                break;
            case "EntityAttackEvent":
                $player = $event->getDamager();
                if (!($player instanceof Player)) return;
                break;
            case "InventoryPickupItemEvent":
                $inventory = $event->getInventory();
                if (!($inventory instanceof PlayerInventory)) return;
                $player = $inventory->getHolder();
                break;
            default:
                $this->getOwner()->getLogger()->error($eventname);
                return;
        }
        $manager = $this->getOwner()->getEventManager();
        $data = $manager->getFromEvent($eventname);
        foreach ($data as $key => $value) {
            $value = $manager->get($key, ["eventname" => $eventname]);
            $manager->executeIfMatchCondition(
                $player,
                $value["if"],
                $value["match"],
                $value["else"],
                [
                    "player" => $player,
                    "eventname" => $eventname,
                    "event" => $event
                ]
            );
        }
    }

    public function interact(PlayerInteractEvent $event) {
        $player = $event->getPlayer();
        if (!isset($this->touch[$player->getName()])) $this->touch[$player->getName()] = 0;
        $tick = Server::getInstance()->getTick();
        if ($tick - $this->touch[$player->getName()] < 3) {
            return;
        }
        $this->touch[$player->getName()] = $tick;
        $this->onEvent($event, "PlayerInteractEvent");

        $manager = $this->getOwner()->getBlockManager();
        $block = $event->getBlock();
        $pos = $manager->getPosition($block);
        if ($player->isOp()) {
            if (($session = Session::getSession($player))->isValid()) {
                $type = $session->get("action");
                $manager = $this->getOwner()->getBlockManager();
                switch ($type) {
                    case 'edit':
                        $session->set("if_key", $pos);
                        if ($manager->exists($pos)) {
                            $data = $manager->get($pos);
                        } else {
                            $data = $manager->repairIF([]);
                            $manager->set($pos);
                        }
                        (new Form)->sendEditIfForm($player, $data);
                        return;
                    case 'check':
                        $pos = $manager->getPosition($block);
                        if (!$manager->exists($pos)) {
                            $player->sendMessage(Language::get("if.block.notFound"));
                            return;
                        }
                        $data = $manager->get($pos);
                        $mes = IFAPI::createIFMessage($data["if"], $data["match"], $data["else"]);
                        $player->sendMessage($mes);
                        break;
                    case 'copy':
                        $pos = $manager->getPosition($block);
                        if (!$manager->exists($pos)) {
                            $player->sendMessage(Language::get("if.block.notFound"));
                            return;
                        }
                        $session->set("if_key", $pos);
                        $session->set("action", "paste");
                        $player->sendMessage(Language::get("if.block.paste"));
                        return;
                    case 'paste':
                        $pos = $manager->getPosition($block);
                        if ($manager->exists($pos)) {
                            $player->sendMessage(Language::get("if.block.alreadyExists"));
                            return;
                        }
                        $manager->set($pos, $manager->get($session->get("if_key")));
                        $player->sendMessage(Language::get("if.block.paste.success"));
                        break;
                    case 'del':
                        $pos = $manager->getPosition($block);
                        if (!$manager->exists($pos)) {
                            $player->sendMessage(Language::get("if.block.notFound"));
                            return;
                        }
                        $session->set("if_key", $pos);
                        (new Form())->confirmDelete($player, [new Form(), "onDeleteIf"]);
                        return;
                }
                $session->setValid(false);
                return;
            }
        }
        if ($manager->exists($pos)) {
            $data = $manager->get($pos);
            $manager->executeIfMatchCondition(
                $player,
                $data["if"],
                $data["match"],
                $data["else"],
                [
                    "player" => $player,
                    "block" => $block,
                    "event" => $event
                ]
            );
        }
    }


    public function receive(DataPacketReceiveEvent $event) {
        $pk = $event->getPacket();
        $player = $event->getPlayer();
        if ($pk instanceof ModalFormResponsePacket) {
            $json = str_replace([",]",",,"], [",\"\"]",",\"\","], $pk->formData);
            $data = json_decode($json);
            Form::onReceive($pk->formId, $player, $data);
        } elseif ($pk instanceof InteractPacket) {
            if ($pk->action === InteractPacket::ACTION_LEAVE_VEHICLE) {
                SetSitting::leave($player);
            }
        }
    }

    public function teleport(EntityTeleportEvent $event) {
        $player = $event->getEntity();
        if ($player instanceof Player) SetSitting::leave($player);
    }
}