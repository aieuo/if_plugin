<?php

namespace aieuo\ip\condition;

use aieuo\ip\utils\Language;
use aieuo\ip\recipe\IFRecipe;

abstract class Condition implements Conditionable, ConditionNames {
    /** @var string */
    protected $id;
    /** @var string */
    protected $detail;
    /** @var int */
    protected $category;

    /**
     * @return string
     */
    public function getId(): string {
        return $this->id;
    }

    public function getDetail(): string {
        $detail = $this->detail;
        if (($detail[0] ?? "") === "@") {
            $detail = Language::get(substr($detail, 1));
        }
        return $detail;
    }

    public function getCategory(): int {
        return $this->category;
    }

    /**
     * @return array
     */
    abstract public function serializeContents(): array;

    public function jsonSerialize(): array {
        return [
            "type" => IFRecipe::CONTENT_TYPE_CONDITION,
            "id" => $this->id,
            "contents" => $this->serializeContents(),
        ];
    }

    public static function parseFromSaveData(array $content): ?self {
        $process = ConditionFactory::get($content["id"]);
        if ($process === null) return null;
        return $process->parseFromConditionSaveData($content["contents"]);
    }
}