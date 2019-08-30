<?php

namespace aieuo\ip\action\process;

use aieuo\ip\utils\Language;
use pocketmine\Player;

class SendMessage extends Process {
    protected $id = self::SEND_MESSAGE;
    protected $name = "@process.sendmessage.name";

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

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get("process.sendmessage.detail", [$this->getMessage()]);
    }

    public function isDataValid(): bool {
        return is_string($this->getMessage()) and $this->getMessage() !== "";
    }

    public function execute(Player $player): ?bool {
        if (!$this->isDataValid()) {
            $player->sendMessage(Language::get("input.invalid", [$this->getName()]));
            return false;
        }
        $player->sendMessage($this->message);
        return true;
    }

    public function serializeContents(): array {
        return [$this->getMessage()];
    }

    public function parseFromProcessSaveData(array $content): ?self {
        if (empty($content[0]) or !is_string($content[0])) return null;
        $this->setMessage($content[0]);
        return $this;
    }
}