<?php

namespace aieuo\ip\form\elements;

class Toggle extends Element {
    /** @var string */
    protected $type = "toggle";

    /** @var boolean */
    private $default = false;

    public function __construct(string $text, bool $default = false) {
        parent::__construct($text);
        $this->default = $default;
    }

    /**
     * @param boolean $default
     * @return self
     */
    public function setDefault(bool $default): self {
        $this->default = $default;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getDefault(): bool {
        return $this->default;
    }

    public function jsonSerialize(): array {
        return [
            "type" => $this->type,
            "text" => $this->checkTranslate($this->text),
            "default" => $this->default,
        ];
    }
}