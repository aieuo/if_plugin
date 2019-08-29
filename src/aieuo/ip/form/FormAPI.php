<?php

namespace aieuo\ip\form;

use pocketmine\Player;

use aieuo\ip\form\base\Form;
use aieuo\ip\form\base\ModalForm;
use aieuo\ip\form\base\ListForm;
use aieuo\ip\form\base\CustomForm;

class FormAPI {
    /** @var array */
    private static $previous = [];

    public static function createModalForm(string $title = ""): ModalForm {
        return new ModalForm($title);
    }

    public static function createListForm(string $title = ""): ListForm {
        return new ListForm($title);
    }

    public static function createCustomForm(string $title = ""): CustomForm {
        return new CustomForm($title);
    }

    public static function sendForm(Player $player, Form $form) {
        $player->sendForm($form);
    }
}