<?php

namespace aieuo\ip\action;

use pocketmine\Player;
use aieuo\ip\recipe\IFRecipe;

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

    /**
     * @param Player $player
     * @param IFRecipe $recipe
     * @param boolean $newAction
     * @param array $messages
     * @return void
     */
    public function sendEditForm(Player $player, IFRecipe $recipe, bool $newAction = true, array $messages = []);

    /**
     * @param array $data
     * @return array
     */
    public function parseFromFormData(array $data): array;
}