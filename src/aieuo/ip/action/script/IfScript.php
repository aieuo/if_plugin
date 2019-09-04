<?php

namespace aieuo\ip\action\script;

use pocketmine\Player;
use aieuo\ip\utils\Categories;
use aieuo\ip\recipe\IFRecipe;
use aieuo\ip\form\elements\Button;
use aieuo\ip\form\IFForm;
use aieuo\ip\form\FormAPI;
use aieuo\ip\condition\Conditionable;
use aieuo\ip\condition\Condition;
use aieuo\ip\action\process\Process;
use aieuo\ip\action\Action;
use aieuo\ip\form\ScriptForm;
use aieuo\ip\Session;

class IfScript extends Script implements Action {
    protected $id = self::SCRIPT_IF;
    protected $name = "@script.if.name";
    protected $category = Categories::CATEGORY_ACTION_SCRIPT;

    /** @var Conditionable[] */
    private $conditions = [];
    /** @var array */
    private $actions = [[]];

    public function __construct(array $conditions = [], array $actions = [], ?string $customName = null) {
        $this->conditions = $conditions;
        $this->setCustomName($customName);
        $this->actions[0] = $actions;
    }

    public function addCondition(Conditionable $condition) {
        $this->conditions[] = $condition;
    }

    public function getCondition(int $index): ?Conditionable {
        return $this->conditions[$index] ?? null;
    }

    public function getConditions(): array {
        return $this->conditions;
    }

    public function removeCondition(int $index) {
        unset($this->conditions[$index]);
        $this->conditions = array_merge($this->conditions);
    }

    public function addAction(Action $action) {
        $this->actions[0][] = $action;
    }

    public function getAction(int $index): ?Action {
        return $this->actions[0][$index] ?? null;
    }

    public function getActions(): array {
        return $this->actions[0];
    }

    public function removeAction(int $index) {
        unset($this->actions[0][$index]);
        $this->actions[0] = array_merge($this->actions[0]);
    }

    public function getDetail(): string {
        $details = ["", "==============if=============="];
        foreach ($this->conditions as $condition) {
            $details[] = $condition->getDetail();
        }
        $details[] = "~~~~~~~~~~~~~~~~~~~~~~~~~~~";
        foreach ($this->actions[0] as $action) {
            $details[] = $action->getDetail();
        }
        $details[] = "================================";
        return implode("\n", $details);
    }

    public function execute(Player $player): ?bool {
        $matched = true;
        foreach ($this->conditions as $condition) {
            $result = $condition->execute($player);
            if ($result === null) return null;
            if (!$result) $matched = false;
        }
        if (!$matched) return true;

        foreach ($this->actions[0] as $action) {
            $action->execute($player);
        }
        return true;
    }

    public function sendEditForm(Player $player, bool $newAction = true, array $parentScripts = [], array $messages = []) {
        $detail = $this->getDetail();
        FormAPI::createListForm($this->getName())
            ->setContent(empty($detail) ? "@form.editIF.empty" : $detail)
            ->addButton(
                new Button("@form.back"),
                new Button("@condition.add"),
                new Button("@script.if.editAction"),
                new Button("@form.action.delete")
            )->onRecive(function (Player $player, ?int $data) use ($parentScripts, $newAction) {
                $session = Session::getSession($player);
                if ($data === null) {
                    $session->setValid(false);
                    return;
                }
                $parentRecipe = $session->get("parent_recipe");
                $parentScript = end($parentScripts);
                if ($newAction and $parentScript === false) {
                    $parentRecipe->addAction($this);
                } elseif ($newAction) {
                    $parentScript[0]->addAction($this, $parentScript[1] ?? 0);
                }
                switch ($data) {
                    case 0:
                        if ($parentScript === false) {
                            (new IFForm)->sendEditContentsForm($player, $parentRecipe);
                            break;
                        }
                        array_pop($parentScripts);
                        $parentScript[0]->sendEditForm($player, false, $parentScripts);
                        break;
                    case 1:
                        (new ScriptForm)->sendEditConditionForm($player, $this, $parentScripts);
                        break;
                    case 2:
                        $session->set("scriptIfActionType", 0);
                        (new ScriptForm)->sendEditIfScriptActionForm($player, $this, $parentScripts);
                        break;
                    case 3:
                        $form = (new IFForm)->getConfirmDeleteForm();
                        if ($parentScript === false) {
                            $form->addArgs($parentRecipe)->onRecive([new IFForm, "onDeleteContent"])->show($player);
                            break;
                        }
                        $key = array_search($this, $parentScript[0]->getActions($session->get("scriptIfActionType", 0)));
                        $form->addArgs($parentScript[0], $parentScripts, $key)->onRecive([new ScriptForm, "onDeleteScript"])->show($player);
                        break;
                }
            })->show($player);
    }

    public function parseFromFormData(array $data): array {
        return ["status" => true, "contents" => [], "cancel" => false, "delete" => false, "errors" => []];
    }

    public function parseFromActionSaveData(array $contents): ?self {
        if (!isset($contents[1])) return null;
        foreach ($contents[0] as $content) {
            switch ($content["type"]) {
                case IFRecipe::CONTENT_TYPE_CONDITION:
                    $condition = Condition::parseFromSaveData($content);
                    break;
                case IFRecipe::CONTENT_TYPE_SCRIPT:
                    $condition = Script::parseFromSaveData($content);
                    break;
                default:
                    return null;
            }
            if ($condition === null) return null;
            $this->addCondition($condition);
        }
        foreach ($contents[1] as $content) {
            switch ($content["type"]) {
                case IFRecipe::CONTENT_TYPE_PROCESS:
                    $action = Process::parseFromSaveData($content);
                    break;
                case IFRecipe::CONTENT_TYPE_SCRIPT:
                    $action = Script::parseFromSaveData($content);
                    break;
                default:
                    return null;
            }
            if ($action === null) return null;
            $this->addAction($action);
        }
        return $this;
    }

    public function serializeContents(): array {
        return  [
            $this->conditions,
            $this->actions[0]
        ];
    }
}