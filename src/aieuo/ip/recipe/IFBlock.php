<?php

namespace aieuo\ip\recipe;

use aieuo\ip\manager\IFManager;
use aieuo\ip\Session;

abstract class IFBlock implements \JsonSerializable {
    /** @var IFRecipe[] */
    protected $recipes = [];

    /** @var string */
    protected $name;

    /** @var boolean */
    protected $changed = false;

    public function __construct(string $name, array $recipes = []) {
        $this->name = $name;
        $this->recipes = $recipes;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @param IFRecipe $recipe
     * @return void
     */
    public function addRecipe(IFRecipe $recipe) {
        $this->recipes[] = $recipe;
        $this->changed = true;
    }

    /**
     * @return IFRecipe[]
     */
    public function getAllRecipe(): array {
        return $this->recipes;
    }

    /**
     * @param int $index
     * @return IFRecipe|null
     */
    public function getRecipe(int $index): ?IFRecipe {
        return $this->recipes[$index] ?? null;
    }

    /**
     * @param int $index
     * @return IFRecipe|null
     */
    public function removeRecipe(int $index) {
        unset($this->recipes[$index]);
    }

    /**
     * @param string $dir
     * @return void
     */
    public function save(string $dir) {
        file_put_contents($dir.$this->name.".json", json_encode($this, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING));
        $this->changed = false;
    }

    abstract public function jsonSerialize(): array;


    /**
     * @param Session $session
     * @param string $name
     * @return IFBlock|null
     */
    public static function getBySession(Session $session, string $name): ?IFBlock {
        $type = $session->get("if_type");
        if ($type === null) return null;
        switch ($type) {
            case IFManager::BLOCK:
                $manager = new BlockIFBlock($name);
                break;
            // case IFManager::COMMAND:
            //     $manager = IFPlugin::getInstance()->getCommandManager();
            //     break;
            // case IFManager::EVENT:
            //     $manager = IFPlugin::getInstance()->getEventManager();
            //     break;
            // case IFManager::CHAIN:
            //     $manager = IFPlugin::getInstance()->getChainManager();
            //     break;
            // case IFManager::FORM:
            //     $manager = IFPlugin::getInstance()->getFormIFManager();
            //     break;
            default:
                $manager = null;
        }
        return $manager;
    }
}