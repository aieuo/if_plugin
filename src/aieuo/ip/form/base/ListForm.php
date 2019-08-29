<?php

namespace aieuo\ip\form\base;

use aieuo\ip\form\elements\Button;

class ListForm extends Form {
    /** @var string */
    private $content = "";
    /** @var Button[] */
    private $buttons = [];

    /**
     * @param string $content
     * @return self
     */
    public function setContent(string $content): self {
        $this->content = $content;
        return $this;
    }

    /**
     * @param Button[] $buttons
     * @return self
     */
    public function addButton(Button ...$buttons): self {
        $this->buttons = array_merge($this->buttons, $buttons);
        return $this;
    }

    /**
     * @param array $buttons
     * @return self
     */
    public function setButtons(array $buttons): self {
        $this->buttons = $buttons;
        return $this;
    }

    public function jsonSerialize(): array {
        $form = [
            "type" => "form",
            "title" => $this->checkTranslate($this->title),
            "content" => $this->checkTranslate($this->content),
            "buttons" => $this->buttons
        ];
        $form = $this->reflectErrors($form);
        return $form;
    }

    public function reflectErrors(array $form): array {
        if (!empty($this->errors)) {
            $form["content"] = implode("\n", array_keys($this->errors))."\n".$form["content"];
        }
        if (!empty($this->highlights)) {
            for ($i=0; $i<count($form["buttons"]); $i++) {
                $button = $form["buttons"][$i];
                $button->setText(($this->highlights[$i] ?? "").$button->getText());
            }
        }
        return $form;
    }
}