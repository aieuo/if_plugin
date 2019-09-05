<?php

namespace aieuo\ip\condition;

use aieuo\ip\utils\Language;
use aieuo\ip\utils\Categories;
use aieuo\ip\form\elements\Toggle;
use aieuo\ip\form\elements\Label;
use aieuo\ip\form\elements\Input;
use aieuo\ip\form\FormAPI;

abstract class TypeMoney extends Condition {
    protected $category = Categories::CATEGORY_CONDITION_MONEY;

    /** @var int */
    private $amount;

    public function __construct(int $amount = null) {
        $this->amount = $amount;
    }

    public function setAmount(int $amount): self {
        $this->amount = $amount;
        return $this;
    }

    public function getAmount(): ?int {
        return $this->amount;
    }

    public function isDataValid(): bool {
        return $this->amount !== null and $this->amount > 0;
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getAmount()]);
    }

    public function getEditForm(array $messages = [], array $default = []) {
        return FormAPI::createCustomForm($this->getName())->addErrors($messages)
            ->addContent(
                new Label($this->getDescription()),
                new Input(Language::get("condition.overmoney.form.amount"), Language::get("input.example", ["100"]), $default[0] ?? strval($this->getAmount() ?? "")),
                new Toggle(Language::get("form.cancel"))
            );
    }

    public function parseFromFormData(array $data): array {
        $status = true;
        $errors = [];
        if ($data[1] === "") {
            $status = false;
            $errors = [["@form.insufficient", 1]];
        } elseif (!is_numeric($data[1])) {
            $status = false;
            $errors = [["@condition.money.notNumber", 1]];
        } elseif ((int)$data[1] <= 0) {
            $status = false;
            $errors = [["@condition.overmoney.zero", 1]];
        }
        return ["status" => $status, "contents" => [(int)$data[1]], "cancel" => $data[2], "delete" => $data[3] ?? false, "errors" => $errors];
    }

    public function parseFromConditionSaveData(array $content): ?self {
        if (!isset($content[0]) or !is_int($content[0])) return null;
        $this->setAmount($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getAmount()];
    }
}