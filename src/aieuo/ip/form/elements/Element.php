<?php

namespace aieuo\ip\form\elements;

use aieuo\ip\utils\Language;

abstract class Element implements \JsonSerializable {
    /** @var string */
    protected $type;
    /** @var string */
    protected $text = "";

    public function __construct(string $text) {
        $this->text = $text;
    }

    /**
     * @param string $text
     * @return self
     */
    public function setText(string $text): self {
        $this->text = $text;
        return $this;
    }

    /**
     * @return string
     */
    public function getText(): string {
        return $this->text;
    }

    /**
     * @param string $text
     * @return string
     */
    public function checkTranslate(string $text): string {
        if (($text[0] ?? "") === "@") {
            $text = Language::get(substr($text, 1));
        }
        return $text;
    }

    /**
     * @return array
     */
    abstract public function jsonSerialize(): array;
}