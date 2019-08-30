<?php

namespace aieuo\ip\action\script;

use pocketmine\Player;
use aieuo\ip\utils\Categories;
use aieuo\ip\recipe\IFRecipe;
use aieuo\ip\condition\Conditionable;
use aieuo\ip\condition\Condition;
use aieuo\ip\action\Action;

class IfScript extends Script implements Action {
    protected $id = self::SCRIPT_IF;
    protected $name = "@script.if.name";
    protected $category = Categories::CATEGORY_ACTION_SCRIPT;

    /** @var Conditionable[] */
    private $conditions = [];
    /** @var IFRecipe */
    private $actionTrue;

    public function __construct(Conditionable ...$conditions) {
        $this->conditions = $conditions;
        $this->actionTrue = new IFRecipe("onTrue");
    }

    public function getDetail(): string {
        $details = ["==========if=========="];
        foreach ($this->conditions as $condition) {
            $details[] = $condition->getDetail();
        }
        $details[] = "~~~~~~~~~~~~~~~~~~~~~~~~";
        $details[] = $this->actionTrue->getDetail();
        $details[] = "========================";
        return implode("\n", $details);
    }

    public function addCondition(Conditionable $condition) {
        $this->conditions[] = $condition;
    }

    public function addAction(Action $action) {
        $this->actionTrue->addAction($action);
    }

    public function execute(Player $player): ?bool {
        $matched = true;
        foreach ($this->conditions as $condition) {
            $result = $condition->execute($player);
            if ($result === null) return null;
            if (!$result) $matched = false;
        }
        if (!$matched) return true;

        $this->actionTrue->execute($player);
        return true;
    }

    public function serializeContents(): array {
        return  [
            $this->conditions,
            $this->actionTrue
        ];
    }

    public function parseFromScriptSaveData(array $contents): ?self {
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
        $actions = (new IFRecipe("onTrue"))->parseFromSaveData($contents[1]);
        if ($actions === null) return null;
        $this->actionTrue = $actions;
        return $this;
    }
}