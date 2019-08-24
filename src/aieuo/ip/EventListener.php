<?php

namespace aieuo\ip;

use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\network\mcpe\protocol\InteractPacket;
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
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\Server;
use pocketmine\Player;

use aieuo\ip\processes\SetSitting;
use aieuo\ip\form\Form;
use aieuo\ip\Session;
use aieuo\ip\IFPlugin;
use aieuo\ip\IFAPI;

class EventListener implements Listener {

    /** @var IFPlugin */
    private $owner;

    public function __construct($owner) {
        $this->owner = $owner;
    }

    private function getOwner(): IFPlugin {
        return $this->owner;
    }

    public function commandProcess(PlayerCommandPreprocessEvent $event) {
        $this->onEvent($event, "PlayerCommandPreprocessEvent");

        if ($event->isCancelled()) return;
        $cmd = mb_substr($event->getMessage(), 1);
        $manager = $this->getOwner()->getCommandManager();
        if ($manager->exists($cmd)) {
            if ($manager->isSubCommand($cmd) and !$manager->exists($cmd)) {
                $subcommands = implode(" | ", $manager->getSubcommands($cmd));
                $event->getPlayer()->sendMessage("Usage: /".$manager->getOriginCommand($cmd)." <".$subcommands.">");
                return;
            }
            $datas = $manager->get($cmd);
            if ($datas["permission"] == "ifplugin.customcommand.op" and !$event->getPlayer()->isOp()) return;
            $manager->executeIfMatchCondition(
                $event->getPlayer(),
                $datas["if"],
                $datas["match"],
                $datas["else"],
                [
                    "player" => $event->getPlayer(),
                    "command" => $cmd,
                    "event" => $event
                ]
            );
        }
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

    public function entityDeath(EntityDeathEvent $event) {
        $this->onEvent($event, "EntityDeathEvent");

        $player = $event->getEntity();
        if ($player instanceof Player) SetSitting::leave($player);
    }

    public function entityLevelChange(EntityLevelChangeEvent $event) {
        $this->onEvent($event, "EntityLevelChangeEvent");

        $player = $event->getEntity();
        if ($player instanceof Player) SetSitting::leave($player);
    }

    public function onEvent($event, $eventname) {
        switch ($eventname) {
            case 'CraftItemEvent':
            case 'PlayerInteractEvent':
            case 'PlayerChatEvent':
            case 'PlayerCommandPreprocessEvent':
            case 'PlayerJoinEvent':
            case 'PlayerQuitEvent':
            case 'PlayerToggleFlightEvent':
            case 'PlayerDropItemEvent':
            case 'BlockBreakEvent':
            case 'BlockPlaceEvent':
                $player = $event->getPlayer();
                break;
            case 'EntityDamageEvent':
            case 'EntityDeathEvent':
            case 'EntityLevelChangeEvent':
                $player = $event->getEntity();
                if (!($player instanceof Player)) return;
                break;
            case "EntityAttackEvent":
                $player = $event->getDamager();
                if (!($player instanceof Player)) return;
                break;
        }
        $manager = $this->getOwner()->getEventManager();
        $datas = $manager->getFromEvent($eventname);
        foreach ($datas as $key => $data) {
            $data = $manager->get($key, ["eventname" => $eventname]);
            $manager->executeIfMatchCondition(
                $player,
                $data["if"],
                $data["match"],
                $data["else"],
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
        if ($tick - $this->touch[$player->getName()] <= 3) {
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
                            $datas = $manager->get($pos);
                        } else {
                            $datas = $manager->repairIF([]);
                            $manager->set($pos);
                        }
                        $mes = IFAPI::createIFMessage($datas["if"], $datas["match"], $datas["else"]);
                        $form = (new Form)->getEditIfForm($mes, $datas["name"] ?? null);
                        Form::sendForm($player, $form, new Form(), "onEditIf");
                        return;
                    case 'check':
                        $pos = $manager->getPosition($block);
                        if (!$manager->exists($pos)) {
                            $player->sendMessage("そのブロックには追加されていません");
                            return;
                        }
                        $datas = $manager->get($pos);
                        $mes = IFAPI::createIFMessage($datas["if"], $datas["match"], $datas["else"]);
                        $player->sendMessage($mes);
                        break;
                    case 'copy':
                        $pos = $manager->getPosition($block);
                        if (!$manager->exists($pos)) {
                            $player->sendMessage("そのブロックには追加されていません");
                            return;
                        }
                        $session->set("if_key", $pos);
                        $session->set("action", "paste");
                        $player->sendMessage("貼り付けるブロックを触ってください");
                        return;
                    case 'paste':
                        $pos = $manager->getPosition($block);
                        if ($manager->exists($pos)) {
                            $player->sendMessage("そのブロックにはすでに追加されています");
                            return;
                        }
                        $manager->set($pos, $manager->get($session->get("if_key")));
                        $player->sendMessage("貼り付けました");
                        break;
                    case 'del':
                        $pos = $manager->getPosition($block);
                        if (!$manager->exists($pos)) {
                            $player->sendMessage("そのブロックには追加されていません");
                            return;
                        }
                        $session->set("if_key", $pos);
                        $form = (new Form())->getConfirmDeleteForm();
                        Form::sendForm($player, $form, new Form(), "onDeleteIf");
                        return;
                }
                $session->setValid(false);
                return;
            }
        }
        if ($manager->exists($pos)) {
            $datas = $manager->get($pos);
            $manager->executeIfMatchCondition(
                $player,
                $datas["if"],
                $datas["match"],
                $datas["else"],
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
            Form::onRecive($pk->formId, $player, $data);
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