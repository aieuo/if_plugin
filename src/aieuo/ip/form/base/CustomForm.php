<?php

namespace aieuo\ip\form\base;

use aieuo\ip\form\elements\Element;

class CustomForm extends Form {
    /** @var Element[] */
    private $contents = [];

    /**
     * @param array $contents
     * @return self
     */
    public function setContents(array $contents): self {
        $this->contents = $contents;
        return $this;
    }

    /**
     * @param Element[] $contents
     * @return self
     */
    public function addContent(Element ...$contents): self {
        $this->contents = array_merge($this->contents, $contents);
        return $this;
    }

    public function jsonSerialize(): array {
        $form = [
            "type" => "custom_form",
            "title" => $this->checkTranslate($this->title),
            "content" => $this->contents
        ];
        $form = $this->reflectErrors($form);
        return $form;
    }

    public function reflectErrors(array $form): array {
        if (!empty($this->errors) and !empty($this->contents)) {
            $form["content"][0]->setText(implode("\n", array_keys($this->errors))."\n".$form["content"][0]->getText());
        }
        for ($i=0; $i<count($form["content"]); $i++) {
            if (empty($this->highlights[$i])) continue;
            $content = $form["content"][$i];
            $content->setText("§e".preg_replace("/§[a-f0-9]/", "", $content->getText()));
        }
        return $form;
    }
}