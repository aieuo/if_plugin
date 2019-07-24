<?php

namespace aieuo\ip;

use pocketmine\Server;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\Player;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerToggleFlightEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\inventory\CraftItemEvent;

use aieuo\ip\ifAPI;
use aieuo\ip\form\Form;
use aieuo\ip\utils\Messages;
use aieuo\ip\Session;

class EventListener implements Listener {
	public function __construct($owner){
		$this->owner = $owner;
	}

    public function getOwner(){
        return $this->owner;
    }

    public function commandProcess(PlayerCommandPreprocessEvent $event){
        $this->onEvent($event, "PlayerCommandPreprocessEvent");

        if($event->isCancelled()) return;
        $cmd = mb_substr($event->getMessage(), 1);
        $manager = $this->getOwner()->getCommandManager();
        if($manager->isAdded($cmd)){
            if($manager->isSubCommand($cmd) and !$manager->isAdded($cmd)) {
                $event->getPlayer()->sendMessage("Usage: /".$manager->getOriginCommand($cmd)." <".implode(" | ", $manager->getSubcommands($cmd)).">");
                return;
        }
            $datas = $manager->get($cmd);
            if($datas["permission"] == "ifplugin.customcommand.op" and !$event->getPlayer()->isOp()) return;
            $manager->executeIfMatchCondition($event->getPlayer(),
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

    public function chat(PlayerChatEvent $event){
        $this->onEvent($event, "PlayerChatEvent");
    }

    public function join(PlayerJoinEvent $event){
        Session::register($event->getPlayer());

        $this->onEvent($event, "PlayerJoinEvent");
    }

    public function craft(CraftItemEvent $event){
        $this->onEvent($event, "CraftItemEvent");
    }

    public function quit(PlayerQuitEvent $event){
        $this->onEvent($event, "PlayerQuitEvent");
    }

    public function toggleFlight(PlayerToggleFlightEvent $event){
        $this->onEvent($event, "PlayerToggleFlightEvent");
    }

    public function blockBreak(BlockBreakEvent $event){
        $this->onEvent($event, "BlockBreakEvent");
    }

    public function blockPlace(BlockPlaceEvent $event){
        $this->onEvent($event, "BlockPlaceEvent");
    }

    public function entityDamage(EntityDamageEvent $event){
        $this->onEvent($event, "EntityDamageEvent");
    }
    public function entityDamageByEntity(EntityDamageByEntityEvent $event){
        $this->onEvent($event, "EntityAttackEvent");
    }
    public function entityDeath(EntityDeathEvent $event){
        $this->onEvent($event, "EntityDeathEvent");
    }

    public function entityLevelChange(EntityLevelChangeEvent $event){
        $this->onEvent($event, "EntityLevelChangeEvent");
    }

    public function onEvent($event, $eventname){
        switch ($eventname) {
            case 'CraftItemEvent':
            case 'PlayerInteractEvent':
            case 'PlayerChatEvent':
            case 'PlayerCommandPreprocessEvent':
            case 'PlayerJoinEvent':
            case 'PlayerQuitEvent':
            case 'PlayerToggleFlightEvent':
            case 'BlockBreakEvent':
            case 'BlockPlaceEvent':
                $player = $event->getPlayer();
                break;
            case 'EntityDamageEvent':
            case 'EntityDeathEvent':
            case 'EntityLevelChangeEvent':
                $player = $event->getEntity();
                if(!($player instanceof Player)) return;
                break;
            case "EntityAttackEvent":
                $player = $event->getDamager();
                if(!($player instanceof Player)) return;
                break;
        }
        $manager = $this->getOwner()->getEventManager();
        $datas = $manager->getFromEvent($eventname);
        foreach ($datas as $key => $data) {
            $data = $manager->get($key, ["eventname" => $eventname]);
            $manager->executeIfMatchCondition($player,
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

	public function interact(PlayerInteractEvent $event){
        $player = $event->getPlayer();
        if(!isset($this->touch[$player->getName()])) $this->touch[$player->getName()] = 0;
        $tick = Server::getInstance()->getTick();
        if($tick - $this->touch[$player->getName()] <= 3) {
            return;
        }
        $this->touch[$player->getName()] = $tick;
		$this->onEvent($event, "PlayerInteractEvent");

        $manager = $this->getOwner()->getBlockManager();
        $block = $event->getBlock();
        $pos = $manager->getPosition($block);
		if($player->isOp()){
			if(($session = Session::get($player))->isValid()){
				$type = $session->getData("action");
				$manager = $this->getOwner()->getBlockManager();
				switch ($type) {
					case 'edit':
                        $session->setData("if_key", $pos);
                        if($manager->isAdded($pos)) {
                            $datas = $manager->get($pos);
                        } else {
                            $datas = $manager->repairIF([]);
                            $manager->set($pos);
                        }
                        $mes = Messages::createMessage($datas["if"], $datas["match"], $datas["else"]);
                        $form = (new Form)->getEditIfForm($mes);
                        Form::sendForm($player, $form, new Form(), "onEditIf");
						return;
					case 'check':
	                    $pos = $manager->getPosition($block);
	                    if(!$manager->isAdded($pos)){
	                        $player->sendMessage("そのブロックには追加されていません");
	                        return;
	                    }
	                    $datas = $manager->get($pos);
	                    $mes = Messages::createMessage($datas["if"], $datas["match"], $datas["else"]);
	                    $player->sendMessage($mes);
						break;
                    case 'copy':
                        $pos = $manager->getPosition($block);
                        if(!$manager->isAdded($pos)){
                            $player->sendMessage("そのブロックには追加されていません");
                            return;
                        }
                        $session->setData("if_key", $pos);
                        $session->setData("action", "paste");
                        $player->sendMessage("貼り付けるブロックを触ってください");
                        return;
                    case 'paste':
                        $pos = $manager->getPosition($block);
                        if($manager->isAdded($pos)){
                            $player->sendMessage("そのブロックにはすでに追加されています");
                            return;
                        }
                        $manager->set($pos, $manager->get($session->getData("if_key")));
                        $player->sendMessage("貼り付けました");
                        break;
					case 'del':
	                    $pos = $manager->getPosition($block);
	                    if(!$manager->isAdded($pos)){
	                        $player->sendMessage("そのブロックには追加されていません");
	                        return;
	                    }
                        $form = (new Form())->getConfirmDeleteForm();
                        Form::sendForm($player, $form, new Form(), "onDeleteIf");
						break;
				}
				$session->setValid(false);
                return;
			}
		}
        if($manager->isAdded($pos)){
            $datas = $manager->get($pos);
            $manager->executeIfMatchCondition($player,
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


    public function Receive(DataPacketReceiveEvent $event){
        $pk = $event->getPacket();
        $player = $event->getPlayer();
        $name = $player->getName();
        if(!$player->isOp())return;
        if($pk instanceof ModalFormResponsePacket){
            $json = str_replace([",]",",,"], [",\"\"]",",\"\","], $pk->formData);
            $data = json_decode($json);
            Form::onRecive($pk->formId, $player, $data);
        }
    }
}