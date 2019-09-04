<?php

namespace aieuo\ip\condition;

use aieuo\ip\utils\Language;
use aieuo\ip\recipe\IFRecipe;
use aieuo\ip\form\elements\Toggle;
use aieuo\ip\form\elements\Label;
use aieuo\ip\form\FormAPI;

abstract class Condition implements Conditionable, ConditionNames {
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

    public function isDataValid(): bool {
        return true;
    }

    public function getEditForm(array $messages = []) {
        return FormAPI::createCustomForm($this->getName())->addErrors($messages)
            ->addContent(
                new Label($this->getDescription()),
                new Toggle(Language::get("form.cancel"))
            );
    }

    public function parseFromFormData(array $data): array {
        return ["status" => true, "contents" => [], "cancel" => $data[1], "delete" => $data[2] ?? false, "errors" => []];
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