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
    public function chat(PlayerChatEvent $event){
        $this->onEvent($event, "PlayerChatEvent");
    }
    public function commandProcess(PlayerCommandPreprocessEvent $event){
        $this->onEvent($event, "PlayerCommandPreprocessEvent");

        if($event->isCancelled()) return;
        $manager = $this->getOwner()->getCommandManager();
        if($manager->isAdded(mb_substr($event->getMessage(), 1))){
            $datas = $manager->get(mb_substr($event->getMessage(), 1));
            $manager->executeIfMatchCondition($event->getPlayer(),
                $datas["if"],
                $datas["match"],
                $datas["else"],
                [
                    "player" => $event->getPlayer(),
                    "command" => $event->getMessage(),
                    "event" => $event
                ]
            );
        }
    }
    public function join(PlayerJoinEvent $event){
        $this->onEvent($event, "PlayerJoinEvent");

        $player = $event->getPlayer();
        if($player->isOp()){
            $session = new Session();
            $player->ifSession = $session;
        }
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
                if(!$player instanceof Player)return;
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
        if(!isset($player->IFLastTouch)) $player->IFLastTouch = 0;
        $tick = Server::getInstance()->getTick();
        if($tick - $player->IFLastTouch <= 3) {
            return;
        }
        $player->IFLastTouch = $tick;
		$this->onEvent($event, "PlayerInteractEvent");

        $manager = $this->getOwner()->getBlockManager();
        $block = $event->getBlock();
        $pos = $manager->getPosition($block);
		if($player->isOp()){
			if(($session = $player->ifSession)->isValid()){
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
            return;

            if($pk->formId === Form::getFormId("SelectEventActionForm")){
                if($data === null) {
                   return;
                }
                $session = $player->ifSession;
                $session->setValid();
                $session->setIfType(Session::EVENT);
                switch ($data) {
                    case 0:
                        $session->setData("type", "add");
                        break;
                    case 1:
                        $session->setData("type", "add_empty");
                        break;
                    case 2:
                        $session->setData("type", "edit");
                        break;
                    case 3:
                        $session->setData("type", "check");
                        break;
                    case 4:
                        $session->setData("type", "del");
                        break;
                    case 5:
                        $session->setValid(false);
                        $player->sendMessage("キャンセルしました");
                        break;
                }
                $form = Form::getSelectEventForm();
                Form::sendForm($player, $form, Form::getFormId("SelectEventForm"));
            }

            if($pk->formId === Form::getFormId("SelectEventForm")){
                $session = $player->ifSession;
                if($data === null) {
                    $session->setValid(false, false);
                    return;
                }
                $eventname = ifAPI::getEventName($data[0]);
                $session->setData("event", $eventname);
                $type = $session->getData("type");
                $manager = $this->getOwner()->getEventManager();
                switch ($type) {
                    case 'add':
                        $form = Form::getAddIfForm(true);
                        Form::sendForm($player, $form, Form::getFormId("AddIfForm"));
                        $session->setData("if_key", null);
                        break;
                    case 'add_empty':
                        $manager->add_empty($eventname);
                        $player->sendMessage("追加しました");
                        $session->setValid(false);
                        break;
                    case 'edit':
                    case 'check':
                    case 'del':
                        $all = $manager->getFromEvent($eventname);
                        $datas = [];
                        foreach ($all as $key => $value) {
                            $datas[] = $manager->get($key, ["eventname" => $eventname]);
                        }
                        $form = Form::getEditEventForm($eventname, $datas);
                        Form::sendForm($player, $form, Form::getFormId("EditEventForm"));
                        break;
                }
            }

            if($pk->formId === Form::getFormId("EditEventForm")){
                $session = $player->ifSession;
                if($data === null) {
                    $session->setValid(false, false);
                    return;
                }
                $type = $session->getData("type");
                $manager = $this->getOwner()->getEventManager();
                $datas = $manager->get($data, ["eventname" => $session->getData("event")]);
                if($data == $manager->getCount($session->getData("event"))){
                    $form = Form::getAddIfForm(true);
                    Form::sendForm($player, $form, Form::getFormId("AddIfForm"));
                    $session->setData("if_key", null);
                    return;
                }
                switch ($type) {
                    case 'edit':
                        $mes = Messages::createMessage($datas["if"], $datas["match"], $datas["else"]);
                        $form = Form::getEditIfForm($mes);
                        Form::sendForm($player, $form, Form::getFormId("EditIfForm"));
                        $session->setData("if_key", $data);
                        break;
                    case 'check':
                        $mes = Messages::createMessage($datas["if"], $datas["match"], $datas["else"]);
                        $player->sendMessage($mes);
                        $session->setValid(false);
                        break;
                    case 'del':
                        $manager->remove($data);
                        $player->sendMessage("削除しました");
                        $session->setValid(false);
                        break;
                }
            }
        }
    }
}