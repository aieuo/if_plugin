<?php

namespace aieuo\ip\formAPI\element;

class Button extends Element {
    public function jsonSerialize(): array {
        return [
            "text" => $this->checkTranslate($this->text),
            "id" => $this->getUUId(),
        ];
    }
}