<?php

namespace aieuo\mineflow\formAPI;

use aieuo\mineflow\formAPI\element\Button;

class ListForm extends Form {

    protected $type = self::LIST_FORM;

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
     * @return string
     */
    public function getContent(): string {
        return $this->content;
    }

    /**
     * @param Button $button
     * @return self
     */
    public function addButton(Button $button): self {
        $this->buttons[] = $button;
        return $this;
    }

    /**
     * @param Button[] $buttons
     * @return self
     */
    public function addButtons(array $buttons): self {
        $this->buttons = array_merge($this->buttons, $buttons);
        return $this;
    }

    /**
     * @param Button[] $buttons
     * @return self
     */
    public function setButtons(array $buttons): self {
        $this->buttons = $buttons;
        return $this;
    }

    /**
     * @return Button[]
     */
    public function getButtons(): array {
        return $this->buttons;
    }

    public function getButton(int $index): ?Button {
        return $this->buttons[$index] ?? null;
    }

    public function jsonSerialize(): array {
        $form = [
            "type" => "form",
            "title" => $this->checkTranslate($this->title),
            "content" => $this->checkTranslate($this->content),
            "buttons" => $this->buttons
        ];
        if (!empty($this->getRecipes())) $form["recipes"] = $this->getRecipes();
        $form = $this->reflectErrors($form);
        return $form;
    }

    public function reflectErrors(array $form): array {
        if (!empty($this->messages)) {
            $form["content"] = implode("\n", array_keys($this->messages))."\n".$form["content"];
        }
        return $form;
    }
}