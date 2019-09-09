<?php

namespace aieuo\ip\utils;

use pocketmine\entity\Attribute;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityIds;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\UpdateAttributesPacket;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\Player;

class Bossbar {
    /** @var array */
    public static $bars = [];

    /** @var float */
    private $max = 1;
    /** @var float */
    private $per = 1;
    /** @var string */
    private $title = "";
    /** @var int */
    private $entityId;

    public function __construct(string $title, float $max = 1, float $per = 1) {
        $this->title = $title;
        $this->max = $max;
        $this->per = $per;
        $this->entityId = Entity::$entityCount++;
    }

    public function setTitle(string $title) {
        $this->title = $title;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function setMax(float $max) {
        $this->max = $max;
    }

    public function getMax(): float {
        return $this->max;
    }

    public function setPercentage(float $per) {
        if ($per > $this->max) $per = $this->max;
        $this->per = $per;
    }

    public function getPercentage(): float {
        return $this->per;
    }

    public function getEntityId(): int {
        return $this->entityId;
    }

    public static function add(Player $player, string $id, string $title, float $max, float $per) {
        if (isset(self::$bars[$player->getName()][$id])) self::remove($player, $id);
        $bar = new Bossbar($title, $max, $per);
        self::$bars[$player->getName()][$id] = $bar;

        $pk = new AddActorPacket();
        $pk->entityRuntimeId = $bar->getEntityId();
        $pk->type = EntityIds::SHULKER;
        $pk->metadata = [
            Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, (1 << Entity::DATA_FLAG_INVISIBLE) | (1 << Entity::DATA_FLAG_IMMOBILE)],
            Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $title]
        ];
        $pk->position = new Vector3(0, 0, 0);
        $player->sendDataPacket($pk);

        $pk2 = new BossEventPacket();
        $pk2->bossEid = $bar->getEntityId();
        $pk2->eventType = BossEventPacket::TYPE_SHOW;
        $pk2->title = $title;
        $pk2->healthPercent = $per;
        $pk2->color = 0;
        $pk2->overlay = 0;
        $pk2->unknownShort = 0;
        $player->sendDataPacket($pk2);
    }

    public static function remove(Player $player, string $id) {
        if (!isset(self::$bars[$player->getName()][$id])) return;
        $bar = self::$bars[$player->getName()][$id];
        $pk = new BossEventPacket();
        $pk->bossEid = $bar->getEntityId();
        $pk->eventType = BossEventPacket::TYPE_HIDE;
        $player->sendDataPacket($pk);

        $pk2 = new RemoveActorPacket();
        $pk2->entityUniqueId = $bar->getEntityId();
        $player->sendDataPacket($pk2);

        unset(self::$bars[$player->getName()][$id]);
    }
}