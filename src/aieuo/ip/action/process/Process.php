<?php

namespace aieuo\ip\action\process;

use pocketmine\Player;
use aieuo\ip\utils\Language;
use aieuo\ip\recipe\IFRecipe;
use aieuo\ip\form\elements\Toggle;
use aieuo\ip\form\elements\Label;
use aieuo\ip\form\IFForm;
use aieuo\ip\form\FormAPI;
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

    public function isDataValid(): bool {
        return true;
    }

    public function sendEditForm(Player $player, IFRecipe $recipe, bool $newAction = true, array $messages = []) {
        $form = FormAPI::createCustomForm($this->getName())->addMessages($messages)->addArgs($recipe, $this)
            ->addContent(
                new Label($this->getDescription()),
                new Toggle(Language::get("form.cancel"))
            );
        if ($newAction) {
            $form->onRecive([new IFForm, "onAddActionForm"])->show($player);
            return;
        }
        $form->addContent(new Toggle(Language::get("form.action.delete")))
            ->onRecive([new IFForm, "onUpdateActionForm"])->show($player);
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
            "type" => IFRecipe::CONTENT_TYPE_PROCESS,
            "id" => $this->getId(),
            "contents" => $this->serializeContents(),
        ];
    }

    public static function parseFromSaveData(array $content): ?self {
        $process = ProcessFactory::get($content["id"]);
        if ($process === null) return null;
        return $process->parseFromActionSaveData($content["contents"]);
    }
}