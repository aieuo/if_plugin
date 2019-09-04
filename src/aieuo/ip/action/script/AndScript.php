<?php

namespace aieuo\ip\action\script;

use pocketmine\Player;
use aieuo\ip\utils\Categories;
use aieuo\ip\recipe\IFRecipe;
use aieuo\ip\form\elements\Button;
use aieuo\ip\form\ScriptForm;
use aieuo\ip\form\IFForm;
use aieuo\ip\form\FormAPI;
use aieuo\ip\condition\Conditionable;
use aieuo\ip\condition\Condition;
use aieuo\ip\Session;

class AndScript extends Script implements Conditionable {
    protected $id = self::SCRIPT_AND;
    protected $name = "@script.and.name";
    protected $category = Categories::CATEGORY_CONDITION_SCRIPT;

    /** @var Conditionable[] */
    protected $conditions = [];

    public function __construct(array $conditions = [], ?string $customName = null) {
        $this->conditions = $conditions;
        $this->setCustomName($customName);
    }

    public function addCondition(Conditionable $condition): self {
        $this->conditions[] = $condition;
        return $this;
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

    public function getDetail(): string {
        $details = ["----------and-----------"];
        foreach ($this->conditions as $condition) {
            $details[] = $condition->getDetail();
        }
        $details[] = "------------------------";
        return implode("\n", $details);
    }

    public function execute(Player $player): ?bool {
        $matched = true;
        foreach ($this->conditions as $condition) {
            $result = $condition->execute($player);
            if ($result === null) return null;
            if (!$result) $matched = false;
        }
        return $matched;
    }

    public function sendEditForm(Player $player, bool $newAction = true, array $parentScripts = [], array $messages = []) {
        $detail = $this->getDetail();
        FormAPI::createListForm($this->getName())
            ->setContent(empty($detail) ? "@form.editIF.empty" : $detail)
            ->addButton(
                new Button("@form.back"),
                new Button("@condition.add"),
                new Button("@form.action.delete")
            )->onRecive(function (Player $player, ?int $data) use ($parentScripts, $newAction) {
                $session = Session::getSession($player);
                var_dump($parentScripts);
                $parentScript = end($parentScripts);
                if ($data === null or $parentScript === false) {
                    $session->setValid(false);
                    return;
                }
                if ($newAction) {
                    $parentScript[0]->addCondition($this);
                }
                switch ($data) {
                    case 0:
                        array_pop($parentScripts);
                        $parentScript[0]->sendEditForm($player, false, $parentScripts);
                        break;
                    case 1:
                        (new ScriptForm)->sendEditConditionForm($player, $this, $parentScripts);
                        break;
                    case 4:
                        $form = (new IFForm)->getConfirmDeleteForm();
                        $key = array_search($this, $parentScript[0]->getConditions());
                        $form->addArgs($parentScript[0], $parentScripts, $key)->onRecive([new ScriptForm, "onDeleteScript"])->show($player);
                        break;
                }
            })->show($player);
    }

    public function parseFromFormData(array $data): array {
        return ["status" => true, "contents" => [], "cancel" => false, "delete" => false, "errors" => []];
    }

    public function parseFromActionSaveData(array $contents): ?self {
        foreach ($contents as $content) {
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
        return $this;
    }

    public function serializeContents(): array {
        return $this->conditions;
    }
}