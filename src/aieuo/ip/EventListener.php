<?php

namespace aieuo\ip;

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
use pocketmine\event\CraftItemEvent;

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

        $manager = $this->getOwner()->getCommandManager();
        if($manager->isAdded(mb_substr($event->getMessage(), 1))){
            $datas = $manager->get(mb_substr($event->getMessage(), 1));
            $manager->executeIfMatchCondition($event->getPlayer(), $datas["if"], $datas["match"], $datas["else"]);
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
    public function onEvent($event, $eventname){
        switch ($eventname) {
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
                $player = $event->getEntity();
                if(!$player instanceof Player)return;
                break;
        }
        $manager = $this->getOwner()->getEventManager();
        $datas = $manager->getByEvent($eventname);
        $manager->setOptions($eventname, $event);
        foreach ($datas as $key => $data) {
            $data = $manager->get($key);
            $manager->executeIfMatchCondition($player, $data["if"], $data["match"], $data["else"]);
        }
    }

	public function interact(PlayerInteractEvent $event){
		$this->onEvent($event, "PlayerInteractEvent");

		$player = $event->getPlayer();
        $manager = $this->getOwner()->getBlockManager();
        $block = $event->getBlock();
        $pos = $manager->getPosition($block);
		if($player->isOp()){
			if(($session = $player->ifSession)->isValid()){
				$type = $session->getData("type");
				$manager = $this->getOwner()->getBlockManager();
				switch ($type) {
					case 'add':
                        if($manager->isAdded($pos)){
                            $player->sendMessage("そのブロックにはすでに追加されています");
                            return;
                        }
                        $form = Form::getAddIfForm();
                        Form::sendForm($player, $form, Form::getFormId("AddIfForm"));
                        $session->setData("if_key", $pos);
						return;
					case 'add_empty':
                        if($manager->isAdded($pos)){
                            $player->sendMessage("そのブロックにはすでに追加されています");
                            return;
                        }
						$manager->set($manager->getPosition($block));
	                    $player->sendMessage("追加しました");
						break;
					case 'edit':
	                    if(!$manager->isAdded($pos)){
	                        $player->sendMessage("そのブロックには追加されていません");
	                        return;
	                    }
	                    $datas = $manager->get($pos);
	                    $mes = Messages::createMessage($datas["if"], $datas["match"], $datas["else"]);
	                    $form = Form::getEditIfForm($mes);
	                    Form::sendForm($player, $form, Form::getFormId("EditIfForm"));
	                    $session->setData("if_key", $pos);
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
	                    $manager->remove($pos);
	                    $player->sendMessage("削除しました");
						break;
				}
				$session->setValid(false);
                return;
			}
		}
        if($manager->isAdded($pos)){
            $datas = $manager->get($pos);
            $manager->executeIfMatchCondition($player, $datas["if"], $datas["match"], $datas["else"]);
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
            if($pk->formId === Form::getFormId("SelectIfTypeForm")){
                if($data === null) {
                   return;
                }
                $session = $player->ifSession;
                switch ($data) {
                	case 0:
		        		$form = Form::getSelectBlockActionForm();
		        		Form::sendForm($player, $form, Form::getFormId("SelectBlockActionForm"));
                		break;
                	case 1:
                        $form = Form::getSelectCommandActionForm();
                        Form::sendForm($player, $form, Form::getFormId("SelectCommandActionForm"));
                		break;
                	case 2:
                        $form = Form::getSelectEventActionForm();
                        Form::sendForm($player, $form, Form::getFormId("SelectEventActionForm"));
                		break;
                }
            }

            if($pk->formId === Form::getFormId("SelectBlockActionForm")){
                if($data === null) {
                   return;
                }
                $session = $player->ifSession;
                $session->setValid();
                $session->setIfType(Session::BLOCK);
                switch ($data) {
                	case 0:
                        $session->setData("type", "add");
                        $player->sendMessage("追加するブロックを触ってください");
                		break;
                	case 1:
						$session->setData("type", "add_empty");
                		$player->sendMessage("追加するブロックを触ってください");
                		break;
                	case 2:
						$session->setData("type", "edit");
                		$player->sendMessage("編集するブロックを触ってください");
                		break;
                	case 3:
						$session->setData("type", "check");
                		$player->sendMessage("確認するブロックを触ってください");
                		break;
                	case 4:
						$session->setData("type", "del");
                		$player->sendMessage("削除するブロックを触ってください");
                		break;
                	case 5:
                		$session->setValid(false);
                		$player->sendMessage("キャンセルしました");
                		break;
                }
            }

            if($pk->formId === Form::getFormId("EditIfForm")){
                $session = $player->ifSession;
                if($data === null) {
                	$session->setValid(false, false);
                    return;
                }
                $type = $session->getIfType();
                if($type === Session::BLOCK){
                	$manager = $this->getOwner()->getBlockManager();
                }elseif($type === Session::COMMAND){
                    $manager = $this->getOwner()->getCommandManager();
                }elseif($type === Session::EVENT){
                    $manager = $this->getOwner()->getEventManager();
                }
                $key = $session->getData("if_key");
                $datas = $manager->get($key);
                if($data == 0){
                    $form = Form::getEditContentsForm($datas["if"]);
                    $session->setData("type", "if");
                }elseif($data == 1){
                    $form = Form::getEditContentsForm($datas["match"]);
                    $session->setData("type", "match");
                }elseif($data == 2){
                    $form = Form::getEditContentsForm($datas["else"]);
                    $session->setData("type", "else");
                }else{
                    $manager->remove($key);
                    $player->sendMessage("削除しました");
                    $session->setValid(false);
                    return;
                }
	            Form::sendForm($player, $form, Form::getFormId("EditIfContentsForm"));
            }

            if($pk->formId === Form::getFormId("EditIfContentsForm")){
                $session = $player->ifSession;
                if($data === null) {
                    $session->setValid(false, false);
                    return;
                }
                $type = $session->getIfType();
                $event = false;
                if($type === Session::BLOCK){
                	$manager = $this->getOwner()->getBlockManager();
                }elseif($type === Session::COMMAND){
                    $manager = $this->getOwner()->getCommandManager();
                }elseif($type === Session::EVENT){
                    $manager = $this->getOwner()->getEventManager();
                    $event = true;
                }
                $key = $session->getData("if_key");
                $datas = $manager->get($key);
                if($data == 0){
                    $form = Form::getAddContentsForm($session->getData("type"), $event);
                    Form::sendForm($player, $form, Form::getFormId("AddContentsForm"));
                }else{
                	$if = $datas[$session->getData("type")][--$data];
                    $form = Form::getDetailForm((int)$if["id"], $if["content"]);
                    Form::sendForm($player, $form, Form::getFormId("DetailForm"));
                    $session->setData("num", $data);
                    $session->setData("id", $if["id"]);
                    $session->setData("content", $if["content"]);
                }
            }

            if($pk->formId === Form::getFormId("AddContentsForm")){
                $session = $player->ifSession;
                if($data === null) {
                	$session->setValid(false, false);
                    return;
                }
                $type = $session->getIfType();
                if($type === Session::BLOCK){
                	$manager = $this->getOwner()->getBlockManager();
                }elseif($type === Session::COMMAND){
                    $manager = $this->getOwner()->getCommandManager();
                }elseif($type === Session::EVENT){
                    $manager = $this->getOwner()->getEventManager();
                }
                if($session->getData("type") == "if"){
                	$id = $manager->getIfIdByListNumber($data[0]);
                }else{
                	$id = $manager->getExeIdByListNumber($data[0]);
                }
                $manager->add($session->getData("if_key"), $session->getData("type"), $id, $data[1]);
                $player->sendMessage("追加しました");
            }

            if($pk->formId === Form::getFormId("DetailForm")){
                $session = $player->ifSession;
                if($data === null) {
                	$session->setValid(false, false);
                    return;
                }
                $type = $session->getIfType();
                if($type === Session::BLOCK){
                    $manager = $this->getOwner()->getBlockManager();
                }elseif($type === Session::COMMAND){
                    $manager = $this->getOwner()->getCommandManager();
                }elseif($type === Session::EVENT){
                    $manager = $this->getOwner()->getEventManager();
                }
                if($data == 0){
                    $form = Form::getUpdateContentsForm($session->getData("type"), $session->getData("id"), $session->getData("content"));
                    Form::sendForm($player, $form, Form::getFormId("UpdateContentsForm"));
                }elseif($data == 1){
                    $manager->del($session->getData("if_key"), $session->getData("type"), $session->getData("num"));
                    $player->sendMessage("削除しました");
                }
            }

            if($pk->formId === Form::getFormId("UpdateContentsForm")){
                $session = $player->ifSession;
                if($data === null) {
                    $session->setValid(false, false);
                    return;
                }
                $type = $session->getIfType();
                if($type === Session::BLOCK){
                    $manager = $this->getOwner()->getBlockManager();
                }elseif($type === Session::COMMAND){
                    $manager = $this->getOwner()->getCommandManager();
                }elseif($type === Session::EVENT){
                    $manager = $this->getOwner()->getEventManager();
                }
                $manager->del($session->getData("if_key"), $session->getData("type"), $session->getData("num"));
                $manager->add($session->getData("if_key"), $session->getData("type"), $session->getData("id"), $data[1]);
                $player->sendMessage("変更しました");
            }

            if($pk->formId === Form::getFormId("AddIfForm")){
                $session = $player->ifSession;
                if($data === null) {
                    $session->setValid(false, false);
                    return;
                }
                $id_1 = $this->getOwner()->getAPI()->getIfIdByListNumber($data[0]);
                $id_2 = $this->getOwner()->getAPI()->getExeIdByListNumber($data[1]);
                $id_3 = $this->getOwner()->getAPI()->getExeIdByListNumber($data[2]);
                $form = Form::createIfContentForm($id_1, $id_2, $id_3);
                Form::sendForm($player, $form, Form::getFormId("InputContentsForm"));
                $session->setData("id_1", $id_1);
                $session->setData("id_2", $id_2);
                $session->setData("id_3", $id_3);
            }

            if($pk->formId === Form::getFormId("InputContentsForm")){
                $session = $player->ifSession;
                if($data === null) {
                    $session->setValid(false, false);
                    return;
                }
                $type = $session->getIfType();
                if($type === Session::BLOCK){
                    $manager = $this->getOwner()->getBlockManager();
                }elseif($type === Session::COMMAND){
                    $manager = $this->getOwner()->getCommandManager();
                    $manager->setOptions($session->getData("if_key"), $session->getData("description"), $session->getData("permission"));
                }elseif($type === Session::EVENT){
                    $manager = $this->getOwner()->getEventManager();
                    $manager->setOptions($session->getData("event"));
                    $key = $manager->add_empty();
                    $session->setData("if_key", $key);
                }
                $key = $session->getData("if_key");
                $manager->add($key, "if", $session->getData("id_1"), (string)$data[0]);
                $manager->add($key, "match", $session->getData("id_2"), (string)$data[1]);
                $manager->add($key, "else", $session->getData("id_3"), (string)$data[2]);
                $session->setValid(false);
                $player->sendMessage("追加しました");
            }

            if($pk->formId === Form::getFormId("SelectCommandActionForm")){
                if($data === null) {
                   return;
                }
                $session = $player->ifSession;
                $session->setValid();
                $session->setIfType(Session::COMMAND);
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
                switch ($data) {
                    case 0:
                    case 1:
                        $form = Form::getAddCommandForm();
                        Form::sendForm($player, $form, Form::getFormId("AddCommandForm"));
                        break;
                    case 2:
                    case 3:
                    case 4:
                        $form = Form::getSelectCommandForm();
                        Form::sendForm($player, $form, Form::getFormId("SelectCommandForm"));
                        break;
                }
            }

            if($pk->formId === Form::getFormId("AddCommandForm")){
                $session = $player->ifSession;
                $manager = $this->getOwner()->getCommandManager();
                if($data === null) {
                    $session->setValid(false, false);
                    return;
                }
                if($data[0] === ""){
                    $player->sendMessage("必要事項を入力してください");
                    return;
                }
                if($manager->exists($data[0])){
                    $player->sendMessage("§cそのコマンドは既に使用されています");
                    return;
                }
                if($manager->isAdded($data[0])){
                    $player->sendMessage("§eそのコマンドは既に追加しています");
                    return;
                }
                if($data[1] === "")$data[1] = "ifPluginで追加したコマンドです";
                if($session->getData("type") == "add_empty"){
                    $manager->setOptions($data[0], $data[1], $data[2] == 0 ? "op" : "default");
                    $manager->set($data[0]);
                    $manager->register($data[0], $data[1], $data[2] == 0 ? "op" : "default");
                    $player->sendMessage("追加しました");
                    $session->setValid(false);
                    return;
                }
                $session->setData("if_key", $data[0]);
                $session->setData("description", $data[1]);
                $session->setData("permission", $data[2] == 0 ? "op" : "default");
                $form = Form::getAddIfForm();
                Form::sendForm($player, $form, Form::getFormId("AddIfForm"));
            }

            if($pk->formId === Form::getFormId("SelectCommandForm")){
                $session = $player->ifSession;
                if($data === null) {
                    $session->setValid(false, false);
                    return;
                }
                if($data[0] === ""){
                    $player->sendMessage("必要事項を入力してください");
                    return;
                }
                $session->setData("if_key", $data[0]);
                $type = $session->getData("type");
                $manager = $this->getOwner()->getCommandManager();
                if(!$manager->isAdded($data[0])){
                    $player->sendMessage("そのコマンドはまだ追加されていません");
                    return;
                }
                if($type == "edit"){
                    $datas = $manager->get($data[0]);
                    $mes = Messages::createMessage($datas["if"], $datas["match"], $datas["else"]);
                    $form = Form::getEditIfForm($mes);
                    Form::sendForm($player, $form, Form::getFormId("EditIfForm"));
                }elseif($type == "check"){
                    $datas = $manager->get($data[0]);
                    $mes = Messages::createMessage($datas["if"], $datas["match"], $datas["else"]);
                    $player->sendMessage($mes);
                }elseif($type == "del"){
                    $manager->remove($data[0]);
                    $player->sendMessage("削除しました");
                }
            }

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
                $manager->setOptions($eventname);
                switch ($type) {
                    case 'add':
                        $form = Form::getAddIfForm(true);
                        Form::sendForm($player, $form, Form::getFormId("AddIfForm"));
                        $session->setData("if_key", null);
                        break;
                    case 'add_empty':
                        $manager->add_empty();
                        $player->sendMessage("追加しました");
                        $session->setValid(false);
                        break;
                    case 'edit':
                    case 'check':
                    case 'del':
                        $all = $manager->getByEvent($eventname);
                        $datas = [];
                        foreach ($all as $key => $value) {
                            $datas[] = $manager->get($key);
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
                $manager->setOptions($session->getData("event"));
                $datas = $manager->get($data);
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