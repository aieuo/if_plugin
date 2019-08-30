<?php

namespace aieuo\ip\recipe;

use pocketmine\Player;
use aieuo\ip\action\Action;
use aieuo\ip\action\process\Process;
use aieuo\ip\action\script\Script;

class IFRecipe implements \JsonSerializable {
    const CONTENT_TYPE_PROCESS = "action";
    const CONTENT_TYPE_CONDITION = "condition";
    const CONTENT_TYPE_SCRIPT = "script";

    /** @var string|null */
    private $name;

    /** @var Actions[] */
    private $actions = [];

    public function __construct(string $name = null) {
        $this->name = $name;
    }

    public function setName(string $name) {
        $this->name = $name;
    }

    public function getName(): ?string {
        return $this->name;
    }

    public function getDetail(): string {
        $details = [];
        foreach ($this->getActions() as $action) {
            $details[] = $action->getDetail();
        }
        return implode("\n", $details);
    }

    public function addAction(Action $action) {
        $this->actions[] = $action;
    }

    public function getAction(int $index): ?Action {
        return $this->actions[$index] ?? null;
    }

    public function getActions(): array {
        return $this->actions;
    }

    public function execute(Player $player): ?bool {
        foreach ($this->actions as $action) {
            $action->execute($player);
        }
        return true;
    }

    public function jsonSerialize(): array {
        return $this->actions;
    }

    public function parseFromSaveData(array $datas): ?self {
        foreach ($datas as $content) {
            switch ($content["type"]) {
                case self::CONTENT_TYPE_PROCESS:
                    $action = Process::parseFromSaveData($content);
                    break;
                case self::CONTENT_TYPE_SCRIPT:
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
}