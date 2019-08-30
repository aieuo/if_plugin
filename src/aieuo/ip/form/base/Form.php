<?php

namespace aieuo\ip\form\base;

use pocketmine\form\Form as PMForm;
use pocketmine\Player;
use aieuo\ip\utils\Language;
use pocketmine\utils\TextFormat;

abstract class Form implements PMForm {
    /** @var string */
    protected $title = "";

    /** @var callable|null */
    private $callable = null;

    /** @var array */
    private $args = [];
    /** @var array */
    protected $errors = [];
    /** @var array */
    protected $highlights = [];

    public function __construct(string $title = "") {
        $this->title = $title;
    }

    /**
     * @param string $title
     * @return self
     */
    public function setTitle(string $title): self {
        $this->title = $title;
        return $this;
    }

    /**
     * @param callable $callable
     * @return self
     */
    public function onRecive(callable $callable): self {
        $this->callable = $callable;
        return $this;
    }

    /**
     * @param mixed ...$args
     * @return self
     */
    public function addArgs(...$args): self {
        $this->args = array_merge($this->args, $args);
        return $this;
    }

    /**
     * @param string $error
     * @param integer $key
     * @return self
     */
    public function addError(string $error, int $place = null): self {
        $error = $this->checkTranslate($error);
        $this->errors[TextFormat::RED.$error.TextFormat::WHITE] = true;
        if ($place !== null) $this->highlights[$place] = TextFormat::YELLOW;
        return $this;
    }

    /**
     * @param string[] $messages
     * @return self
     */
    public function addMessages(array $messages): self {
        foreach ($messages as $message) {
            $message = $this->checkTranslate($message);
            $this->errors[$message] = true;
        }
        return $this;
    }

    /**
     * @param array $errors
     * @return self
     */
    public function addErrors(array $errors): self {
        foreach ($errors as $error) {
            if (!is_array($error) or !isset($error[1])) {
                $this->addMessages([$error]);
            } else {
                $this->addError($error[0], $error[1]);
            }
        }
        return $this;
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
     * @param Player $player
     * @return self
     */
    public function show(Player $player): self {
        $player->sendForm($this);
        return $this;
    }

    /**
     * @return array
     */
    abstract public function jsonSerialize(): array;

    /**
     * @param array $form
     * @return array
     */
    abstract public function reflectErrors(array $form): array;

    public function handleResponse(Player $player, $data): void {
        if (!is_callable($this->callable)) return;

        call_user_func_array($this->callable, array_merge([$player, $data], $this->args));
    }
}