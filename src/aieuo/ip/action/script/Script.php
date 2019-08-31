<?php

namespace aieuo\ip\action\script;

use aieuo\ip\utils\Language;
use aieuo\ip\recipe\IFRecipe;

abstract class Script implements ScriptNames {
    /** @var string */
    protected $id;
    /** @var string */
    protected $name;
    /** @var string */
    protected $description;
    /** @var string */
    protected $detail;
    /** @var int */
    protected $category;

    public function getId(): string {
        return $this->id;
    }

    public function getName(): string {
        $name = $this->name;
        if (($name[0] ?? "") === "@") {
            $name = Language::get(substr($name, 1));
        }
        return $name;
    }

    public function getDescription(): string {
        $description = $this->description;
        if (($description[0] ?? "") === "@") {
            $description = Language::get(substr($description, 1));
        }
        return $description;
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
        return $script->parseFromActionSaveData($content["contents"]);
    }
}