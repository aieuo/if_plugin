<?php

namespace aieuo\ip;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\Listener;
use pocketmine\Server;
use aieuo\ip\recipe\BlockIFBlock;
use aieuo\ip\form\IFForm;

class EventListener implements Listener {
    /** @var array */
    private $touch = [];

    /** @var Main */
    private $owner;

    public function __construct(Main $owner) {
        $this->owner = $owner;
    }

    private function getOwner(): Main {
        return $this->owner;
    }

    public function join(PlayerJoinEvent $event) {
        Session::createSession($event->getPlayer());

        // $this->onEvent($event, "PlayerJoinEvent");
    }

    public function onTouch(PlayerInteractEvent $event) {
        $player = $event->getPlayer();
        if (!isset($this->touch[$player->getName()])) $this->touch[$player->getName()] = 0;
        $tick = Server::getInstance()->getTick();
        if ($tick - $this->touch[$player->getName()] < 3) {
            return;
        }
        $this->touch[$player->getName()] = $tick;
        // $this->onEvent($event, "PlayerInteractEvent");

        $block = $event->getBlock();
        $manager = $this->getOwner()->getBlockIFManager();
        $pos = $manager->getPositionAsString($block);
        if ($player->isOp()) {
            $session = Session::getSession($player);
            if ($session->isValid() and ($type = $session->get("blockIF_action")) !== null) {
                $session->remove("blockIF_action");
                switch ($type) {
                    case 'edit':
                        $session->set("if_key", $pos);
                        if (!$manager->exists($pos)) {
                            $manager->set($pos, new BlockIFBlock($pos));
                        }
                        $ifData = $manager->get($pos);
                        (new IFForm)->sendListIFForm($player, $ifData);
                        return;
                    case 'copy':
                        if (!$manager->exists($pos)) {
                            $player->sendMessage(Language::get("if.block.notFound"));
                            return;
                        }
                        $session->set("if_key", $pos)->set("blockIF_action", "paste");
                        $player->sendMessage(Language::get("if.block.paste"));
                        return;
                    case 'paste':
                        if ($manager->exists($pos)) {
                            $player->sendMessage(Language::get("if.block.alreadyExists"));
                            return;
                        }
                        $manager->set($pos, $manager->get($session->get("if_key")));
                        $player->sendMessage(Language::get("if.block.paste.success"));
                        break;
                    case 'del':
                        if (!$manager->exists($pos)) {
                            $player->sendMessage(Language::get("if.block.notFound"));
                            return;
                        }
                        $session->set("if_key", $pos);
                        $form = (new IFForm)->getConfirmDeleteForm();
                        $form->onRecive([new IFForm(), "onDeleteIF"])->show($player);
                        return;
                }
                $session->setValid(false);
                return;
            }
        }
        if ($manager->exists($pos)) {
            $idData = $manager->get($pos);
            foreach ($idData->getAllRecipe() as $recipe) {
                $recipe->execute($player);
            }
        }
    }
}