<?php

namespace aieuo\ip\action\script;

use pocketmine\Player;
use aieuo\ip\recipe\IFRecipe;
use aieuo\ip\condition\Conditionable;
use aieuo\ip\condition\Condition;
use aieuo\ip\utils\Categories;

class AndScript extends Script implements Conditionable {
    protected $id = self::SCRIPT_AND;
    protected $name = "@script.and.name";
    protected $category = Categories::CATEGORY_CONDITION_SCRIPT;

    /** @var Conditionable[] */
    protected $conditions = [];

    public function __construct(Conditionable ...$conditions) {
        $this->conditions = $conditions;
    }

    public function getDetail(): string {
        $details = ["----------and-----------"];
        foreach ($this->conditions as $condition) {
            $details[] = $condition->getDetail();
        }
        $details[] = "------------------------";
        return implode("\n", $details);
    }

    /**
     * @param Conditionable $condition
     * @return self
     */
    public function addCondition(Conditionable $condition): self {
        $this->conditions[] = $condition;
        return $this;
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

    public function serializeContents(): array {
        return $this->conditions;
    }

    public function parseFromScriptSaveData(array $contents): ?self {
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
}