<?php

namespace aieuo\ip\form\elements;

class StepSlider extends Dropdown {
    /** @var string */
    protected $type = "step_slider";

    public function jsonSerialize(): array {
        return [
            "type" => $this->type,
            "text" => $this->checkTranslate($this->text),
            "steps" => $this->options,
            "default" => $this->default,
        ];
    }
}