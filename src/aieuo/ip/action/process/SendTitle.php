<?php

namespace aieuo\ip\action\process;

use pocketmine\Player;
use aieuo\ip\utils\Language;
use aieuo\ip\utils\Categories;
use aieuo\ip\form\elements\Toggle;
use aieuo\ip\form\elements\Label;
use aieuo\ip\form\elements\Input;
use aieuo\ip\form\FormAPI;

class SendTitle extends Process {
    protected $id = self::SEND_TITLE;
    protected $name = "@process.sendtitle.name";
    protected $description = "@process.sendtitle.description";
    protected $detail = "process.sendtitle.detail";
    protected $category = Categories::CATEGRY_ACTION_MESSAGE;

    /** @var string */
    private $title;
    /** @var string */
    private $subtitle;
    /** @var string */
    private $fadeIn = -1;
    /** @var string */
    private $stay = -1;
    /** @var string */
    private $fadeOut = -1;

    public function __construct(string $title = "", string $subtitle = "", int $fadeIn = -1, int $stay = -1, int $fadeOut = -1) {
        $this->title = $title;
        $this->subtitle = $subtitle;
        $this->fadeIn = $fadeIn;
        $this->stay = $stay;
        $this->fadeOut = $fadeOut;
    }

    public function setTitle(string $title, string $subtitle = ""): self {
        $this->title = $title;
        $this->subtitle = $subtitle;
        return $this;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function getSubTitle(): string {
        return $this->subtitle;
    }

    public function setTime(int $fadeIn = -1, int $stay = -1, int $fadeOut = -1): self{
        $this->fadeIn = $fadeIn;
        $this->stay = $stay;
        $this->fadeOut = $fadeOut;
        return $this;
    }

    public function getTime(): array {
        return [$this->fadeIn, $this->stay, $this->fadeOut];
    }

    public function isDataValid(): bool {
        return $this->getTitle() !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getTitle(), $this->getSubTitle()]);
    }

    public function execute(Player $player): ?bool {
        if (!$this->isDataValid()) {
            $player->sendMessage(Language::get("input.invalid", [$this->getName()]));
            return false;
        }
        $player->addTitle($this->getTitle(), $this->getSubTitle(), ...$this->getTime());
        return true;
    }

    public function getEditForm(array $messages = [], array $default = []) {
        return FormAPI::createCustomForm($this->getName())->addErrors($messages)
            ->addContent(
                new Label($this->getDescription()),
                new Input(Language::get("process.sendtitle.form.title"), Language::get("input.example", ["aieuo"]), $default[0] ?? $this->getTitle()),
                new Input(Language::get("process.sendtitle.form.subtitle"), Language::get("input.example", ["aieuo"]), $default[1] ?? $this->getSubTitle()),
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
        return ["status" => $status, "contents" => [$data[1], $data[2]], "cancel" => $data[3], "delete" => $data[4] ?? false, "errors" => $errors];
    }

    public function parseFromActionSaveData(array $content): ?self {
        if (!isset($content[1]) or !is_string($content[0]) or !is_string($content[1])) return null;
        $this->setTitle($content[0], $content[1]);
        if (isset($content[4])) {
            $this->setTime((int)$content[2], (int)$content[3], (int)$content[4]);
        }
        return $this;
    }

    public function serializeContents(): array {
        return array_merge([$this->getTitle(), $this->getSubTitle()], $this->getTime());
    }
}