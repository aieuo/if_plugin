<?php

namespace aieuo\ip\form;

use pocketmine\Player;
use aieuo\ip\recipe\IFRecipe;
use aieuo\ip\manager\IFManager;
use aieuo\ip\form\elements\Button;
use aieuo\ip\form\base\ModalForm;
use aieuo\ip\form\elements\Input;
use aieuo\ip\form\elements\Label;
use aieuo\ip\form\elements\Toggle;
use aieuo\ip\recipe\IFBlock;
use aieuo\ip\Session;
use aieuo\ip\utils\Language;

class IFForm {
    public function getBlockIFForm(): BlockIFForm {
        return new BlockIFForm();
    }

    public function sendSelectIFTypeForm(Player $player) {
        FormAPI::createListForm("@form.selectAction.title")
            ->setContent("@form.selectButton")
            ->addButton(
                new Button("@form.block"),
                new Button("@form.command"),
                new Button("@form.event"),
                new Button("@form.formif"),
                new Button("@form.chain"),
                new Button("@form.import"),
                new Button("@form.exit")
            )->onRecive(function (Player $player, ?int $data) {
                if ($data === null) {
                    return;
                }
                switch ($data) {
                    case 0:
                        $this->getBlockIFForm()->sendSelectActionForm($player);
                        break;
                    case 1:
                        break;
                    case 2:
                        break;
                    case 3:
                        break;
                    case 4:
                        break;
                    case 5:
                        break;
                }
            })->show($player);
    }

    public function sendListIFForm(Player $player, IFBlock $ifData) {
        $form = FormAPI::createListForm("@form.listIF.title")->setContent("@form.selectButton");
        $form->addButton(new Button("@form.back"), new Button("@form.action.add"));
        foreach ($ifData->getAllRecipe() as $key => $recipe) {
            $form->addButton(new Button($recipe->getName() ?? (string)$key));
        }
        $form->onRecive(function (Player $player, ?int $data, IFBlock $ifData) {
            $session = Session::getSession($player);
            if ($data === null) {
                $session->setValid(false);
                return;
            }
            if ($data === 0) {
                $this->sendSelectIFTypeForm($player);
                return;
            }
            if ($data === 1) {
                $recipe = new IFRecipe();
                $ifData->addRecipe($recipe);
                $this->sendEditIFForm($player, $recipe);
                return;
            }
            $data -= 2;
            $recipe = $ifData->getRecipe($data);
            if (!($recipe instanceof IFRecipe)) return; // TODO: error message
            $session->set("if_selected_place", $data);
            $this->sendEditIFForm($player, $recipe);
        })->addArgs($ifData)->show($player);
    }

    public function sendEditIFForm(Player $player, IFRecipe $recipe, array $messages = []) {
        $detail = $recipe->getDetail();
        FormAPI::createListForm($recipe->getName() ?? "@form.editIF.title")
            ->setContent(empty($detail) ? "@form.editIF.empty" : $detail)
            ->addButton(
                new Button("@form.editIF.edit"),
                new Button("@form.action.delete"),
                new Button("@form.editIF.changeName"),
                new Button("@form.editIF.export"),
                new Button("@form.back")
            )->onRecive(function (Player $player, ?int $data, IFRecipe $recipe) {
                $session = Session::getSession($player);
                if ($data === null) {
                    $session->setValid(false);
                    return;
                }
                $manager = IFManager::getBySession($session);
                switch ($data) {
                    case 0:
                        break;
                    case 1:
                        $this->getConfirmDeleteForm()
                            ->onRecive([$this, "onDeleteRecipe"])
                            ->show($player);
                        break;
                    case 2:
                        $this->sendChangeRecipeNameForm($player, $recipe);
                        break;
                    case 3:
                        break;
                    case 4:
                        $key = $session->get("if_key");
                        if ($key === null) break; // TODO: error message
                        if (!$manager->exists($key)) {
                            $manager->set($key, IFBlock::getBySession($session, $key));
                        }
                        $ifData = $manager->get($key);
                        $this->sendListIFForm($player, $ifData);
                        break;
                }
            })->addArgs($recipe)->addMessages($messages)->show($player);
    }

    public function sendChangeRecipeNameForm(Player $player, IFRecipe $recipe) {
        FormAPI::createCustomForm("@form.setName.title")
            ->addContent(
                new Label("@form.setName.content0"),
                new Input("@form.setName.content1", "", $recipe->getName() ?? ""),
                new Toggle("@form.setName.content2")
            )->onRecive(function (Player $player, ?array $data, IFRecipe $recipe) {
                $session = Session::getSession($player);
                if ($data === null) {
                    $session->setValid(false);
                    return;
                }
                if ($data[2]) $data[1] = "";
                $recipe->setName($data[1]);
                $player->sendMessage(Language::get("form.setName.success"));
                $this->sendEditIFForm($player, $recipe, ["@form.setName.success"]);
            })->addArgs($recipe)->show($player);
    }

    public function getConfirmDeleteForm(): ModalForm {
        $form = FormAPI::createModalForm("@form.confirmDelete.title")
                    ->setContent("@form.confirmDelete.content")
                    ->setButton1("@form.confirmDelete.yes")
                    ->setButton2("@form.confirmDelete.no");
        return $form;
    }

    public function onDeleteIF(Player $player, bool $data) {
        $session = Session::getSession($player);
        $manager = IFManager::getBySession($session);

        if ($data) {
            $manager->remove($session->get("if_key"));
            $player->sendMessage(Language::get("form.delete.success"));
        } else {
            $player->sendMessage(Language::get("form.cancelled"));
        }
        $session->setValid(false);
    }

    public function onDeleteRecipe(Player $player, bool $data) {
        $session = Session::getSession($player);
        $manager = IFManager::getBySession($session);

        if ($data) {
            $key = $session->get("if_key");
            $place = $session->get("if_selected_place");
            if ($key === null or $place === null) return; //TODO: error message
            $ifData = $manager->get($key);
            if ($ifData === null) return; //TODO: error message
            $ifData->removeRecipe($place);
            $player->sendMessage(Language::get("form.delete.success"));
        } else {
            $player->sendMessage(Language::get("form.cancelled"));
        }
        $session->setValid(false);
    }
}