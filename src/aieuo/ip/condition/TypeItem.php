<?php

namespace aieuo\ip\condition;

use pocketmine\item\ItemFactory;

use pocketmine\item\Item;
use aieuo\ip\utils\Language;
use aieuo\ip\utils\Categories;
use aieuo\ip\form\elements\Toggle;
use aieuo\ip\form\elements\Label;
use aieuo\ip\form\elements\Input;
use aieuo\ip\form\FormAPI;

abstract class TypeItem extends Condition {
    protected $category = Categories::CATEGORY_CONDITION_ITEM;

    /** @var Item */
    private $item;

    public function __construct(Item $item = null) {
        $this->item = $item;
    }

    public function setItem(Item $item): self {
        $this->item = $item;
        return $this;
    }

    public function getItem(): ?Item {
        return $this->item;
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        $item = $this->getItem();
        return Language::get($this->detail, [$item->getId(), $item->getDamage(), $item->getName(), $item->getCount()]);
    }

    public function isDataValid(): bool {
        return $this->item instanceof Item and $this->item->getCount() > 0;
    }

    public function getEditForm(array $messages = [], array $default = []) {
        $item = $this->getItem();
        $id = "";
        $count = "";
        $name = "";
        if ($item instanceof Item) {
            $id = $item->getId().":".$item->getDamage();
            $count = $item->getCount();
            $name = $item->hasCustomName() ? $item->getName() : "";
        }
        return FormAPI::createCustomForm($this->getName())->addErrors($messages)
            ->addContent(
                new Label($this->getDescription()),
                new Input(Language::get("condition.item.form.id"), Language::get("input.example", ["1:0"]), $default[0] ?? $id),
                new Input(Language::get("condition.item.form.count"), Language::get("input.example", ["5"]), $default[1] ?? $count),
                new Input(Language::get("condition.item.form.name"), Language::get("input.example", ["aieuo"]), $default[2] ?? $name),
                new Toggle(Language::get("form.cancel"))
            );
    }
    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") {
            $errors[] = ["@form.insufficient", 1];
        }
        try {
            ItemFactory::fromString($data[1]);
        } catch (\InvalidArgumentException $e) {
            $errors[] = ["@condition.item.notFound", 1];
        }
        if ($data[2] === "") {
            $errors[] = ["@form.insufficient", 2];
        } elseif (!is_numeric($data[2])) {
            $errors = ["@condition.item.count.notNumber", 2];
        } elseif ((int)$data[2] <= 0) {
            $errors = [["@condition.item.form.zero", 2]];
        }
        $status = empty($errors);
        return ["status" => $status, "contents" => [$data[1], $data[2], $data[3]], "cancel" => $data[4], "delete" => $data[5] ?? false, "errors" => $errors];
    }

    public function parseFromConditionSaveData(array $content): ?self {
        if (!isset($content[1])) return null;
        try {
            $item = ItemFactory::fromString($content[0]);
        } catch (\InvalidArgumentException $e) {
            return null;
        }
        $item->setCount((int)$content[1]);
        if (!empty($content[2])) $item->setCustomName($content[2]);
        $this->setItem($item);
        return $this;
    }

    public function serializeContents(): array {
        $item = $this->getItem();
        return [
            $item->getId().":".$item->getDamage(),
            $item->getCount(),
            $item->hasCustomName() ? $item->getName() : "",
        ];
    }
}