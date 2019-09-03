<?php

namespace aieuo\ip\form;

use pocketmine\Player;

use aieuo\ip\form\base\Form;
use aieuo\ip\form\base\ModalForm;
use aieuo\ip\form\base\ListForm;
use aieuo\ip\form\base\CustomForm;
use aieuo\ip\Session;

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

    public static function sendPrevious(Player $player, bool $same = true, array $args = []) {
        $session = Session::getSession($player);
        $forms = $session->get("form_history", []);
        if ($same) {
            if (empty($forms)) return;
            array_pop($forms);
        }
        if (empty($forms)) return;
        $form = array_pop($forms);
        $form->addArgs(...$args);
        $form->show($player, true);
    }
}