<?php

namespace aieuo\ip\form\elements;

class Button extends Element {
    public function jsonSerialize(): array {
        return [
            "text" => $this->checkTranslate($this->text)
        ];
    }
}