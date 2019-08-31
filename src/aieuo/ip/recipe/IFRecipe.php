<?php

namespace aieuo\ip\recipe;

use pocketmine\Server;
use pocketmine\Player;
use aieuo\ip\action\script\Script;
use aieuo\ip\action\process\Process;
use aieuo\ip\action\Action;

class IFRecipe implements \JsonSerializable {
    const CONTENT_TYPE_PROCESS = "action";
    const CONTENT_TYPE_CONDITION = "condition";
    const CONTENT_TYPE_SCRIPT = "script";

    const TARGET_DEFAULT = 0;
    const TARGET_SPECIFIED = 1;
    const TARGET_BROADCAST = 2;


    /** @var string|null */
    private $name;

    /** @var Actions[] */
    private $actions = [];

    /**
     * @var array
     */
    protected $target;

    public function __construct(string $name = null, array $target = null) {
        $this->name = $name;
        $this->target = $target ?? ["type" => self::TARGET_DEFAULT, "target" => ""];
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

    public function removeAction(int $index) {
        unset($this->actions[$index]);
        $this->actions = array_merge($this->actions);
    }

    public function getActions(): array {
        return $this->actions;
    }

    public function setTarget(int $type, string $target = "") {
        $this->target = ["type" => $type, "target" => $target];
    }

    public function getTargetSetting(): array {
        return $this->target;
    }

    public function getTargets(Player $player): array {
        switch ($this->target["type"]) {
            case self::TARGET_DEFAULT:
                $targets = [$player];
                break;
            case self::TARGET_SPECIFIED:
                $targets = [Server::getInstance()->getPlayer($this->target["target"])];
                break;
            case self::TARGET_BROADCAST:
                $targets = Server::getInstance()->getOnlinePlayers();
                break;
        }
        return $targets;
    }

    public function execute(Player $player): ?bool {
        $targets = $this->getTargets($player);
        foreach ($targets as $target) {
            if (!($target instanceof Player)) continue; // TODO error message
            foreach ($this->actions as $action) {
                $action->execute($target);
            }
        }
        return true;
    }

    public function jsonSerialize(): array {
        return [
            "actions" => $this->actions,
            "target" => $this->target,
        ];
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