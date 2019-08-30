<?php

namespace aieuo\ip;

use pocketmine\utils\Config;
use pocketmine\plugin\PluginBase;
use aieuo\ip\utils\Language;
use aieuo\ip\manager\BlockIFManager;
use aieuo\ip\economy\Economy;
use aieuo\ip\condition\ConditionFactory;
use aieuo\ip\commands\IFCommand;
use aieuo\ip\action\script\ScriptFactory;
use aieuo\ip\action\process\ProcessFactory;

class Main extends PluginBase {
    /** @var Main */
    private static $instance;
    /** @var boolean */
    private $loaded = false;

    public static function getInstance(): self {
        return self::$instance;
    }

    public function onEnable() {
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

        $this->config = new Config($this->getDataFolder()."config.yml", Config::YAML, [
            "language" => "jpn",
        ]);
        $this->config->save();

        $languageName = $this->config->get("language", "jpn");
        $languages = [];
        foreach ($this->getResources() as $resource) {
            $filename = $resource->getFilename();
            if (strrchr($filename, ".") == ".ini") $languages[] = basename($filename, ".ini");
            if ($filename === $languageName.".ini") {
                $messages = parse_ini_file($resource->getPathname());
            }
        }
        if (!isset($messages)) {
            $languageList = implode(", ", $languages);
            switch ($this->getServer()->getLanguage()->getLang()) {
                case "eng":
                    $errors = ["Failed to load language file", "available languages are: [".$languageList."]"];
                    break;
                default:
                    $errors = ["言語ファイルの読み込みに失敗しました", $languageList." が使用できます"];
            }
            foreach ($errors as $error) {
                $this->getLogger()->warning($error);
            }
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }
        $this->language = new Language($messages);

        ProcessFactory::init();
        ScriptFactory::init();
        ConditionFactory::init();

        $this->getServer()->getCommandMap()->register("ifPlugin", new IFCommand($this));

        (new Economy($this))->loadPlugin();

        $this->blockIF = new BlockIFManager($this);

        self::$instance = $this;

        $this->loaded = true;
    }

    public function onDisable() {
        if (!$this->loaded) return;
        $this->blockIF->saveAll();
    }

    public function getBlockIFManager(): BlockIFManager {
        return $this->blockIF;
    }
}