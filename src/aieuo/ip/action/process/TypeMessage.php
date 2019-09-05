<?php

namespace aieuo\ip\action\process;

use aieuo\ip\utils\Language;
use aieuo\ip\utils\Categories;
use aieuo\ip\form\elements\Toggle;
use aieuo\ip\form\elements\Label;
use aieuo\ip\form\elements\Input;
use aieuo\ip\form\FormAPI;

abstract class TypeMessage extends Process {
    protected $category = Categories::CATEGRY_ACTION_MESSAGE;

    /** @var string */
    private $message;

    public function __construct(string $message = "") {
        $this->message = $message;
    }

    public function setMessage(string $message): self {
        $this->message = $message;
        return $this;
    }

    public function getMessage(): string {
        return $this->message;
    }

    public function isDataValid(): bool {
        return $this->getMessage() !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getMessage()]);
    }

    public function getEditForm(array $messages = [], array $default = []) {
        return FormAPI::createCustomForm($this->getName())->addErrors($messages)
            ->addContent(
                new Label($this->getDescription()),
                new Input(Language::get("process.message.form.message"), Language::get("input.example", ["aieuo"]), $default[0] ?? $this->getMessage()),
                new Toggle(Language::get("form.cancel"))
            );
    }

    public function parseFromFormData(array $data): array {
        $status = true;
        $errors = [];
        if ($data[1] === "") {
            $status = false;
            $errors = [["@form.insufficient", 1]];
        }
        return ["status" => $status, "contents" => [$data[1]], "cancel" => $data[2], "delete" => $data[3] ?? false, "errors" => $errors];
    }

    public function parseFromActionSaveData(array $content): ?self {
        if (empty($content[0]) or !is_string($content[0])) return null;
        $this->setMessage($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getMessage()];
    }
}