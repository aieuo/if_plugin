<?php

namespace aieuo\ip\action\process;

use aieuo\ip\utils\Language;
use aieuo\ip\recipe\IFRecipe;
use aieuo\ip\action\Action;

abstract class Process implements Action, ProcessNames {
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

    /**
     * @return string
     */
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

    /**
     * @return array
     */
    abstract public function serializeContents(): array;

    public function jsonSerialize(): array {
        return [
            "type" => IFRecipe::CONTENT_TYPE_PROCESS,
            "id" => $this->getId(),
            "contents" => $this->serializeContents(),
        ];
    }

    public static function parseFromSaveData(array $content): ?self {
        $process = ProcessFactory::get($content["id"]);
        if ($process === null) return null;
        return $process->parseFromProcessSaveData($content["contents"]);
    }
}