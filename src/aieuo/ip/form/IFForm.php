<?php

namespace aieuo\ip\form;

use aieuo\ip\action\Action;
use aieuo\ip\action\process\ProcessFactory;
use aieuo\ip\action\script\ScriptFactory;
use pocketmine\Player;
use aieuo\ip\recipe\IFRecipe;
use aieuo\ip\manager\IFManager;
use aieuo\ip\form\elements\Button;
use aieuo\ip\form\base\ModalForm;
use aieuo\ip\form\elements\Dropdown;
use aieuo\ip\form\elements\Input;
use aieuo\ip\form\elements\Label;
use aieuo\ip\form\elements\Toggle;
use aieuo\ip\recipe\IFBlock;
use aieuo\ip\Session;
use aieuo\ip\utils\Categories;
use aieuo\ip\utils\Language;

class IFForm {
    public function getBlockIFForm(): BlockIFForm {
        return new BlockIFForm();
    }

    public function sendSelectIFTypeForm(Player $player) {
        FormAPI::createListForm("@form.IFType.title")
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

    public function sendListIFForm(Player $player, IFBlock $ifData, array $messages = []) {
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
                $session->set("if_selected_place", count($ifData->getAllRecipe()));
                $this->sendEditIFForm($player, $recipe);
                return;
            }
            $data -= 2;
            $recipe = $ifData->getRecipe($data);
            if (!($recipe instanceof IFRecipe)) return; // TODO: error message
            $session->set("if_selected_place", $data);
            $this->sendEditIFForm($player, $recipe);
        })->addArgs($ifData)->addMessages($messages)->show($player);
    }

    public function sendEditIFForm(Player $player, IFRecipe $recipe, array $messages = []) {
        $detail = $recipe->getDetail();
        FormAPI::createListForm($recipe->getName() ?? "@form.editIF.title")
            ->setContent(empty($detail) ? "@form.editIF.empty" : $detail)
            ->addButton(
                new Button("@form.editIF.edit"),
                new Button("@form.action.delete"),
                new Button("@form.editIF.changeName"),
                new Button("@form.editIF.target"),
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
                        $this->sendEditContentsForm($player, $recipe);
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
                        $this->sendSelectTargetForm($player, $recipe);
                        break;
                    case 4:
                        // TODO: export form
                        break;
                    case 5:
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

    public function sendEditContentsForm(Player $player, IFRecipe $recipe, array $messages = []) {
        $form = FormAPI::createListForm(Language::get("form.editContents.title", [$recipe->getName() ?? "recipe"]))
            ->setContent("@form.selectButton")
            ->addButton(new Button("@form.back"), new Button("@form.editContents.add"));
        foreach ($recipe->getActions() as $action) {
            $form->addButton(new Button($action->getDetail()));
        }
        $form->onRecive(function (Player $player, ?int $data, IFRecipe $recipe) {
            $session = Session::getSession($player);
            if ($data === null) {
                $session->setValid(false);
                return;
            }
            if ($data === 0) {
                $this->sendEditIFForm($player, $recipe);
                return;
            }
            if ($data === 1) {
                $this->sendSelectActionCategoryForm($player, $recipe);
                return;
            }
            $data -= 2;
            $session->set("contents_select_place", $data);
            $action = $recipe->getAction($data);
            if (!($action instanceof Action)) return; // TODO error message
            $action = $action;
            $action->sendEditForm($player, $recipe, false);
        })->addArgs($recipe)->addMessages($messages)->show($player);
    }

    public function sendSelectActionCategoryForm(Player $player, IFRecipe $recipe) {
        $form = FormAPI::createListForm("@form.selectCategory.title")->setContent("@form.selectButton");
        $form->addButton(new Button("@form.back"), new Button("@form.selectCategory.all"));
        $categories = [];
        foreach (Categories::getActionCategories() as $category => $categoryName) {
            $form->addButton(new Button($categoryName));
            $categories[] = $category;
        }
        $form->onRecive(function (Player $player, ?int $data, array $categories, IFRecipe $recipe) {
            $session = Session::getSession($player);
            if ($data === null) {
                $session->setValid(false);
                return;
            }
            if ($data === 0) {
                $this->sendEditContentsForm($player, $recipe);
                return;
            }
            if ($data === 1) {
                $actions = array_merge(ScriptFactory::getAll(), ProcessFactory::getAll());
                $categoryName = Language::get("form.selectCategory.all");
            } else {
                $data -= 2;
                $category = $categories[$data];
                $categoryName = Categories::getActionCategories()[$category];
                $actions = array_merge(ScriptFactory::getByCategory($category), ProcessFactory::getByCategory($category));
            }
            $session->set("category_name", $categoryName)->set("actions", $actions);
            $this->sendSelectActionForm($player, $recipe, $actions, $categoryName);
        })->addArgs($categories, $recipe)->show($player);
    }

    public function sendSelectActionForm(Player $player, IFRecipe $recipe, array $actions, string $categoryName) {
        $form = FormAPI::createListForm(Language::get("form.selectAction.title", [$categoryName]))
            ->setContent("@form.selectButton")->addButton(new Button("@form.back"));
        foreach ($actions as $action) {
            $form->addButton(new Button($action->getName()));
        }
        $form->onRecive(function (Player $player, ?int $data, IFRecipe $recipe, array $actions) {
            $session = Session::getSession($player);
            if ($data === null) {
                $session->setValid(false);
                return;
            }
            if ($data === 0) {
                $this->sendSelectActionCategoryForm($player, $recipe);
                return;
            }
            $data -= 1;
            $action = $actions[$data];
            if (!($action instanceof Action)) return; // TODO: error message
            $action = clone $action;
            $action->sendEditForm($player, $recipe);
        })->addArgs($recipe, $actions)->show($player);
    }

    public function onAddActionForm(Player $player, array $data, IFRecipe $recipe, Action $action) {
        $session = Session::getSession($player);
        if ($data === null) {
            $session->setValid(false);
            return;
        }
        $datas = $action->parseFromFormData($data);
        if ($datas["cancel"]) {
            $this->sendSelectActionForm($player, $recipe, $session->get("actions"), $session->get("category_name"));
            return;
        }
        if ($datas["status"] === null) {
            $action->sendEditForm($player, $recipe, true, $datas["errors"]);
            return;
        }
        $action->parseFromActionSaveData($datas["contents"]);
        $recipe->addAction($action);
        $this->sendEditContentsForm($player, $recipe, ["@form.changed"]);
    }

    public function onUpdateActionForm(Player $player, array $data, IFRecipe $recipe, Action $action) {
        $session = Session::getSession($player);
        if ($data === null) {
            $session->setValid(false);
            return;
        }
        $datas = $action->parseFromFormData($data);
        if ($datas["cancel"]) {
            $this->sendEditIFForm($player, $recipe, ["@form.cancelled"]);
            return;
        }
        if ($datas["delete"]) {
            $form = $this->getConfirmDeleteForm();
            $form->addArgs($recipe)->onRecive([$this, "onDeleteContent"])->show($player);
            return;
        }
        if ($datas["status"] === null) {
            $action->sendEditForm($player, $recipe, false, $datas["errors"]);
            return;
        }
        $action->parseFromActionSaveData($datas["contents"]);
        $this->sendEditContentsForm($player, $recipe, ["@form.changed"]);
    }

    public function sendSelectTargetForm(Player $player, IFRecipe $recipe) {
        $target = $recipe->getTargetSetting();
        FormAPI::createCustomForm("@form.selectTarget.title")
            ->addContent(
                new Dropdown("@form.selectTarget.content0", [
                    Language::get("form.selectTarget.content0.item0"),
                    Language::get("form.selectTarget.content0.item1"),
                    Language::get("form.selectTarget.content0.item2")
                ], $target["type"]),
                new Input("@form.selectTarget.content1", "@form.selectTarget.content1.placeholder", $target["target"])
            )->onRecive(function (Player $player, ?array $data) use ($recipe) {
                $session = Session::getSession($player);
                if ($data === null) {
                    $session->setValid(false);
                    return;
                }
                $recipe->setTarget($data[0], $data[1]);
                $player->sendMessage(Language::get("form.changed"));
                $this->sendEditIFForm($player, $recipe, ["@form.changed"]);
            })->show($player);
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

        $key = $session->get("if_key");
        $place = $session->get("if_selected_place");
        if ($key === null or $place === null) return; //TODO: error message
        $ifData = $manager->get($key);
        if ($ifData === null) return; //TODO: error message
        if ($data) {
            $ifData->removeRecipe($place);
            $player->sendMessage(Language::get("form.delete.success"));
            $this->sendListIFForm($player, $ifData, ["@form.delete.success"]);
        } else {
            $player->sendMessage(Language::get("form.cancelled"));
            $this->sendEditIFForm($player, $ifData->getRecipe($key), ["@form.cancelled"]);
        }
    }

    public function onDeleteContent(Player $player, bool $data, IFRecipe $recipe) {
        $session = Session::getSession($player);
        if ($data) {
            $recipe->removeAction($session->get("contents_select_place"));
            $player->sendMessage(Language::get("form.delete.success"));
            $this->sendEditContentsForm($player, $recipe, ["@form.delete.success"]);
        } else {
            $player->sendMessage(Language::get("form.cancelled"));
            $this->sendEditContentsForm($player, $recipe, ["@form.cancelled"]);
        }
    }
}