<?php

namespace aieuo\ip\action\script;

use aieuo\ip\utils\Language;
use aieuo\ip\recipe\IFRecipe;

abstract class Script implements ScriptNames {
    /** @var string */
    protected $id;
    /** @var string */
    protected $detail;

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

    abstract public function serializeContents(): array;

    public function jsonSerialize(): array {
        return [
            "type" => IFRecipe::CONTENT_TYPE_SCRIPT,
            "id" => $this->getId(),
            "contents" => $this->serializeContents(),
        ];
    }

    public static function parseFromSaveData(array $content): ?self {
        $script = ScriptFactory::get($content["id"]);
        if ($script === null) return null;
        return $script->parseFromScriptSaveData($content["contents"]);
    }
}