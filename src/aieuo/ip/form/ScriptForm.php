<?php

namespace aieuo\ip\form;

use pocketmine\Player;
use aieuo\ip\utils\Language;
use aieuo\ip\utils\Categories;
use aieuo\ip\recipe\IFRecipe;
use aieuo\ip\manager\IFManager;
use aieuo\ip\form\elements\Toggle;
use aieuo\ip\form\elements\Button;
use aieuo\ip\form\base\ModalForm;
use aieuo\ip\condition\ConditionFactory;
use aieuo\ip\condition\Condition;
use aieuo\ip\action\script\ScriptFactory;
use aieuo\ip\action\script\Script;
use aieuo\ip\action\script\IfScript;
use aieuo\ip\action\process\ProcessFactory;
use aieuo\ip\action\Action;
use aieuo\ip\condition\Conditionable;
use aieuo\ip\Session;

class ScriptForm {
    public function sendEditIfScriptActionForm(Player $player, IfScript $script, array $parentScripts = [], array $messages = []) {
        $form = FormAPI::createListForm(Language::get("form.script.editActions.title", [$script->getCustomName()]))
            ->setContent("@form.selectButton")
            ->addButton(new Button("@form.back"), new Button("@form.editContents.add"));
        foreach ($script->getActions() as $action) {
            $form->addButton(new Button(trim($action->getDetail())));
        }

        $form->onRecive(function (Player $player, ?int $data) use ($script, $parentScripts) {
            $session = Session::getSession($player);
            if ($data === null) {
                $session->setValid(false);
                return;
            }
            if ($data === 0) {
                $script->sendEditForm($player, false, $parentScripts);
                return;
            }
            if ($data === 1) {
                $this->sendSelectIfScriptActionCategoryForm($player, $script, $parentScripts);
                return;
            }
            $data -= 2;
            $session->set("contents_select_place", $data);
            $action = $script->getAction($data, $session->get("scriptIfActionType", 0));
            if (!($action instanceof Action)) return; // TODO error message

            if ($action instanceof Script) {
                $parentScripts[] = [$script, $session->get("scriptIfActionType", 0)];
                $action->sendEditForm($player, false, $parentScripts);
                return;
            }
            $action->getEditForm()
                ->addContent(new Toggle("@form.action.delete"))
                ->addArgs($script, $action, $parentScripts)
                ->onRecive([$this, "onUpdateIfScriptActionForm"])
                ->show($player);
        })->addMessages($messages)->show($player, true);
    }

    public function sendSelectIfScriptActionCategoryForm(Player $player, IfScript $script, array $parentScripts) {
        $form = FormAPI::createListForm("@form.selectCategory.title")->setContent("@form.selectButton");
        $form->addButton(new Button("@form.back"), new Button("@form.selectCategory.all"));
        $categories = [];
        foreach (Categories::getActionCategories() as $category => $categoryName) {
            $form->addButton(new Button($categoryName));
            $categories[] = $category;
        }

        $form->onRecive(function (Player $player, ?int $data) use ($categories, $script, $parentScripts) {
            $session = Session::getSession($player);
            if ($data === null) {
                $session->setValid(false);
                return;
            }
            if ($data === 0) {
                $this->sendEditIfScriptActionForm($player, $script, $parentScripts);
                return;
            }
            if ($data === 1) {
                $actions = array_merge(ScriptFactory::getAll(), ProcessFactory::getAll());
                $categoryName = Language::get("form.selectCategory.all");
            } else {
                $category = $categories[$data - 2];
                $actions = array_merge(ScriptFactory::getByCategory($category), ProcessFactory::getByCategory($category));
                $categoryName = Categories::getActionCategories()[$category];
            }
            $session->set("category_name", $categoryName)->set("actions", $actions);
            $this->sendSelectIfScriptActionForm($player, $script, $parentScripts);
        })->show($player);
    }

    public function sendSelectIfScriptActionForm(Player $player, IfScript $script, array $parentScripts) {
        $session = Session::getSession($player);
        $categoryName = $session->get("category_name") ?? "";
        $actions = $session->get("actions") ?? [];
        $form = FormAPI::createListForm(Language::get("form.selectAction.title", [$categoryName]))
            ->setContent("@form.selectButton")->addButton(new Button("@form.back"));
        foreach ($actions as $action) {
            $form->addButton(new Button($action->getName()));
        }

        $form->onRecive(function (Player $player, ?int $data) use ($actions, $script, $parentScripts) {
            $session = Session::getSession($player);
            if ($data === null) {
                $session->setValid(false);
                return;
            }
            if ($data === 0) {
                $this->sendSelectIfScriptActionCategoryForm($player, $script, $parentScripts);
                return;
            }
            $data -= 1;
            $action = $actions[$data];
            if (!($action instanceof Action)) return; // TODO: error message
            $action = clone $action;

            if ($action instanceof Script) {
                $parentScripts[] = [$script, $session->get("scriptIfActionType", 0)];
                $action->sendEditForm($player, true, $parentScripts);
                return;
            }
            $action->getEditForm()
                ->addArgs($script, $action, $parentScripts)
                ->onRecive([$this, "onAddIfScriptActionForm"])
                ->show($player);
        })->show($player);
    }

    public function onAddIfScriptActionForm(Player $player, ?array $data, IfScript $script, Action $action, array $parentScripts) {
        $session = Session::getSession($player);
        if ($data === null) {
            $session->setValid(false);
            return;
        }
        $datas = $action->parseFromFormData($data);
        if ($datas["cancel"]) {
            $this->sendSelectIfScriptActionForm($player, $script, $parentScripts);
            return;
        }
        if ($datas["status"] === false) {
            $action->getEditForm($datas["errors"], $datas["contents"])
                ->addArgs($script, $action, $parentScripts)
                ->onRecive([$this, "onAddIfScriptActionForm"])
                ->show($player);
            return;
        }
        $action->parseFromActionSaveData($datas["contents"]);
        $script->addAction($action, $session->get("scriptIfActionType", 0));
        $this->sendEditIfScriptActionForm($player, $script, $parentScripts, ["@form.changed"]);
    }

    public function onUpdateIfScriptActionForm(Player $player, ?array $data, IfScript $script, Action $action, array $parentScripts) {
        $session = Session::getSession($player);
        if ($data === null) {
            $session->setValid(false);
            return;
        }
        $datas = $action->parseFromFormData($data);
        if ($datas["cancel"]) {
            $this->sendEditIfScriptActionForm($player, $script, $parentScripts, ["@form.cancelled"]);
            return;
        }
        if ($datas["delete"]) {
            $form = (new IFForm)->getConfirmDeleteForm();
            $form->addArgs($script, $parentScripts)->onRecive([$this, "onDeleteAction"])->show($player);
            return;
        }
        if ($datas["status"] === false) {
            $action->getEditForm($datas["errors"], $datas["contents"])
                ->addContent(new Toggle("@form.action.delete"))
                ->addArgs($script, $action, $parentScripts)
                ->onRecive([$this, "onUpdateIfScriptActionForm"])
                ->show($player);
            return;
        }
        $action->parseFromActionSaveData($datas["contents"]);
        $this->sendEditIfScriptActionForm($player, $script, $parentScripts, ["@form.changed"]);
    }

    public function getConfirmDeleteForm(): ModalForm {
        $form = FormAPI::createModalForm("@form.confirmDelete.title")
                    ->setContent("@form.confirmDelete.content")
                    ->setButton1("@form.confirmDelete.yes")
                    ->setButton2("@form.confirmDelete.no");
        return $form;
    }

    public function onDeleteAction(Player $player, bool $data, IfScript $script, array $parentScripts) {
        $session = Session::getSession($player);
        if ($data) {
            $script->removeAction($session->get("contents_select_place"));
            $player->sendMessage(Language::get("form.delete.success"));
            $this->sendEditIfScriptActionForm($player, $script, $parentScripts, ["@form.delete.success"]);
        } else {
            $player->sendMessage(Language::get("form.cancelled"));
            $this->sendEditIfScriptActionForm($player, $script, $parentScripts, ["@form.cancelled"]);
        }
    }

    public function onDeleteScript(Player $player, bool $data, IfScript $script, array $parentScripts, int $place) {
        if ($data) {
            $script->removeAction($place);
            $player->sendMessage(Language::get("form.delete.success"));
            array_pop($parentScripts);
            $this->sendEditIfScriptActionForm($player, $script, $parentScripts, ["@form.delete.success"]);
        } else {
            $player->sendMessage(Language::get("form.cancelled"));
            $this->sendEditIfScriptActionForm($player, $script, $parentScripts, ["@form.cancelled"]);
        }
    }

    public function sendEditConditionForm(Player $player, Script $script, array $parentScripts = [], array $messages = []) {
        $form = FormAPI::createListForm(Language::get("form.script.editConditions.title", [$script->getCustomName()]))
            ->setContent("@form.selectButton")
            ->addButton(new Button("@form.back"), new Button("@form.editContents.add"));
        foreach ($script->getConditions() as $condition) {
            $form->addButton(new Button(trim($condition->getDetail())));
        }

        $form->onRecive(function (Player $player, ?int $data) use ($script, $parentScripts) {
            $session = Session::getSession($player);
            if ($data === null) {
                $session->setValid(false);
                return;
            }
            if ($data === 0) {
                $script->sendEditForm($player, false, $parentScripts);
                return;
            }
            if ($data === 1) {
                $this->sendSelectConditionCategoryForm($player, $script, $parentScripts);
                return;
            }
            $data -= 2;
            $session->set("contents_select_place", $data);
            $condition = $script->getCondition($data);
            if (!($condition instanceof Conditionable)) return; // TODO error message

            if ($condition instanceof Script) {
                $parentScripts[] = [$script];
                $condition->sendEditForm($player, false, $parentScripts);
                return;
            }
            $condition->getEditForm()
                ->addContent(new Toggle("@form.action.delete"))
                ->addArgs($script, $condition, $parentScripts)
                ->onRecive([$this, "onUpdateConditionForm"])
                ->show($player);
        })->addMessages($messages)->show($player, true);
    }

    public function sendSelectConditionCategoryForm(Player $player, Script $script, array $parentScripts) {
        $form = FormAPI::createListForm("@form.selectCategory.title")->setContent("@form.selectButton");
        $form->addButton(new Button("@form.back"), new Button("@form.selectCategory.all"));
        $categories = [];
        foreach (Categories::getConditionCategories() as $category => $categoryName) {
            $form->addButton(new Button($categoryName));
            $categories[] = $category;
        }

        $form->onRecive(function (Player $player, ?int $data) use ($categories, $script, $parentScripts) {
            $session = Session::getSession($player);
            if ($data === null) {
                $session->setValid(false);
                return;
            }
            if ($data === 0) {
                $this->sendEditConditionForm($player, $script, $parentScripts);
                return;
            }
            if ($data === 1) {
                $conditions = array_merge(ScriptFactory::getAll(), ConditionFactory::getAll());
                $categoryName = Language::get("form.selectCategory.all");
            } else {
                $category = $categories[$data - 2];
                $conditions = array_merge(ScriptFactory::getByCategory($category), ConditionFactory::getByCategory($category));
                $categoryName = Categories::getConditionCategories()[$category];
            }
            $session->set("category_name", $categoryName)->set("conditions", $conditions);
            $this->sendSelectConditionForm($player, $script, $parentScripts);
        })->show($player);
    }

    public function sendSelectConditionForm(Player $player, Script $script, array $parentScripts) {
        $session = Session::getSession($player);
        $categoryName = $session->get("category_name") ?? "";
        $conditions = $session->get("conditions") ?? [];
        $form = FormAPI::createListForm(Language::get("form.script.selectCondition.title", [$categoryName]))
            ->setContent("@form.selectButton")->addButton(new Button("@form.back"));
        foreach ($conditions as $condition) {
            $form->addButton(new Button($condition->getName()));
        }

        $form->onRecive(function (Player $player, ?int $data) use ($conditions, $script, $parentScripts) {
            $session = Session::getSession($player);
            if ($data === null) {
                $session->setValid(false);
                return;
            }
            if ($data === 0) {
                $this->sendSelectConditionCategoryForm($player, $script, $parentScripts);
                return;
            }
            $data -= 1;
            $condition = $conditions[$data];
            if (!($condition instanceof Conditionable)) return; // TODO: error message
            $condition = clone $condition;

            if ($condition instanceof Script) {
                $parentScripts[] = [$script];
                $condition->sendEditForm($player, true, $parentScripts);
                return;
            }
            $condition->getEditForm()
                ->addArgs($script, $condition, $parentScripts)
                ->onRecive([$this, "onAddConditionForm"])
                ->show($player);
        })->show($player);
    }

    public function onAddConditionForm(Player $player, ?array $data, Script $script, Conditionable $condition, array $parentScripts) {
        $session = Session::getSession($player);
        if ($data === null) {
            $session->setValid(false);
            return;
        }
        $datas = $condition->parseFromFormData($data);
        if ($datas["cancel"]) {
            $this->sendSelectConditionForm($player, $script, $parentScripts);
            return;
        }
        if ($datas["status"] === false) {
            $condition->getEditForm($datas["errors"], $datas["contents"])
                ->addArgs($script, $condition, $parentScripts)
                ->onRecive([$this, "onAddConditionForm"])
                ->show($player);
            return;
        }
        $condition->parseFromConditionSaveData($datas["contents"]);
        $script->addCondition($condition);
        $this->sendEditConditionForm($player, $script, $parentScripts, ["@form.changed"]);
    }

    public function onUpdateConditionForm(Player $player, ?array $data, Script $script, Conditionable $condition, array $parentScripts) {
        $session = Session::getSession($player);
        if ($data === null) {
            $session->setValid(false);
            return;
        }
        $datas = $condition->parseFromFormData($data);
        if ($datas["cancel"]) {
            $this->sendEditConditionForm($player, $script, $parentScripts, ["@form.cancelled"]);
            return;
        }
        if ($datas["delete"]) {
            $form = (new IFForm)->getConfirmDeleteForm();
            $form->addArgs($script, $parentScripts)->onRecive([$this, "onDeleteCondition"])->show($player);
            return;
        }
        if ($datas["status"] === false) {
            $condition->getEditForm($datas["errors"], $datas["contents"])
                ->addContent(new Toggle("@form.action.delete"))
                ->addArgs($script, $condition, $parentScripts)
                ->onRecive([$this, "onUpdateConditionForm"])
                ->show($player);
            return;
        }
        $condition->parseFromConditionSaveData($datas["contents"]);
        $this->sendEditConditionForm($player, $script, $parentScripts, ["@form.changed"]);
    }

    public function onDeleteCondition(Player $player, bool $data, Script $script, array $parentScripts) {
        $session = Session::getSession($player);
        if ($data) {
            $script->removeCondition($session->get("contents_select_place"));
            $player->sendMessage(Language::get("form.delete.success"));
            $this->sendEditConditionForm($player, $script, $parentScripts, ["@form.delete.success"]);
        } else {
            $player->sendMessage(Language::get("form.cancelled"));
            $this->sendEditConditionForm($player, $script, $parentScripts, ["@form.cancelled"]);
        }
    }
}