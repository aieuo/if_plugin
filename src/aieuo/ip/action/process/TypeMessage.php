<?php

namespace aieuo\ip\action\process;

use pocketmine\Player;
use aieuo\ip\utils\Language;
use aieuo\ip\utils\Categories;
use aieuo\ip\recipe\IFRecipe;
use aieuo\ip\form\elements\Toggle;
use aieuo\ip\form\elements\Label;
use aieuo\ip\form\elements\Input;
use aieuo\ip\form\IFForm;
use aieuo\ip\form\FormAPI;
use aieuo\ip\Session;

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

    public function getMessage(): ?string {
        return $this->message;
    }

    public function isDataValid(): bool {
        return is_string($this->getMessage()) and $this->getMessage() !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getMessage()]);
    }

    public function sendEditForm(Player $player, IFRecipe $recipe, bool $newAction = true, array $messages = []) {
        $form = FormAPI::createCustomForm($this->getName())
            ->addContent(
                new Label($this->getDescription()),
                new Input(Language::get("process.message.form.message"), Language::get("input.example", ["aieuo"]), $this->getMessage() ?? ""),
                new Toggle(Language::get("form.cancel"))
            );
        if (!$newAction) $form->addContent(new Toggle(Language::get("form.action.delete")));
        $form->onRecive(function (Player $player, ?array $data, IFRecipe $recipe) use ($newAction) {
            $session = Session::getSession($player);
            if ($data === null) {
                $session->setValid(false);
                return;
            }
            if ($newAction and $data[2]) {
                (new IFForm)->sendSelectActionForm($player, $recipe, $session->get("actions"), $session->get("category_name"));
                return;
            } elseif ($data[2]) {
                (new IFForm)->sendEditIFForm($player, $recipe, ["@form.cancelled"]);
                return;
            }
            if (!$newAction and $data[3]) {
                $form = (new IFForm)->getConfirmDeleteForm();
                $form->addArgs($recipe)->onRecive([new IFForm, "onDeleteContent"])->show($player);
                return;
            }
            if ($data[1] === "") {
                $this->sendEditForm($player, $recipe, $newAction, [["@form.insufficient", 1]]);
                return;
            }
            $this->setMessage($data[1]);
            $recipe->addAction($this);
            (new IFForm)->sendEditContentsForm($player, $recipe, ["@form.changed"]);
        })->addArgs($recipe)->addErrors($messages)->show($player);
    }

    public function parseFromProcessSaveData(array $content): ?self {
        if (empty($content[0]) or !is_string($content[0])) return null;
        $this->setMessage($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getMessage()];
    }
}