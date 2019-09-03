<?php

namespace aieuo\ip\action;

use pocketmine\Player;

interface Action extends \JsonSerializable {
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getDescription(): string;

    /**
     * @return string
     */
    public function getDetail(): string;

    /**
     * @param Player $player
     * @return boolean|null
     */
    public function execute(Player $player): ?bool;
}