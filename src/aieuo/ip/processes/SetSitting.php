<?php

namespace aieuo\ip\processes;

use pocketmine\Player;
use pocketmine\entity\Entity;
use pocketmine\level\Position;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\types\EntityLink;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;

use aieuo\ip\utils\Language;

class SetSitting extends TypePosition {

    protected $id = self::SET_SITTING;
    protected $name = "@process.setsitting.name";
    protected $description = "@process.setsitting.description";

    private static $entityIds = [];

    public function getMessage() {
        $pos = $this->getPosition();
        if ($pos === false) return false;
        return Language::get("process.setsitting.detail", [$pos->x.",".$pos->y.",".$pos->z.",".$pos->level->getFolderName()]);
    }

    public function execute() {
        $player = $this->getPlayer();
        $pos = $this->getPosition();
        if (!($pos instanceof Position)) {
            $player->sendMessage(Language::get("input.invalid", [$this->getName()]));
            return;
        }
        $pk = new AddActorPacket();
        $pk->entityRuntimeId = ++Entity::$entityCount;
        $pk->type = 84;
        $pk->position = $pos;
        $pk->links = [new EntityLink($pk->entityRuntimeId, $player->getId(), EntityLink::TYPE_RIDER)];
        $pk->metadata = [
            Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, 1 << Entity::DATA_FLAG_INVISIBLE]
        ];
        $player->dataPacket($pk);
}