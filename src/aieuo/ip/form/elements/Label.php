<?php

namespace aieuo\ip\form\elements;

class Label extends Element {
    /** @var string */
    protected $type = "label";

    public function jsonSerialize(): array {
        return [
            "type" => $this->type,
            "text" => $this->checkTranslate($this->text),
        ];
    }
}