<?php

namespace aieuo\ip\manager;

use pocketmine\math\Vector3;
use aieuo\ip\recipe\IFRecipe;
use aieuo\ip\recipe\BlockIFBlock;
use aieuo\ip\Main;

class BlockIFManager extends IFManager {
    public function __construct(Main $owner) {
        parent::__construct($owner, "blocks");
    }

    public function loadIFs() {
        $files = glob($this->saveDir."/*.json");
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data === null) continue;
            if (!isset($data["key"]) or !isset($data["recipes"])) continue;

            $key = $data["key"];
            $recipes = [];
            foreach ($data["recipes"] as $recipeName => $ifData) {
                $recipe = (new IFRecipe($recipeName))->parseFromSaveData($ifData);
                var_dump($recipe, $recipe->getDetail());
                $recipes[] = $recipe;
            }
            $this->set($key, new BlockIFBlock($key, $recipes));
        }
    }

    public function addRecipe(string $key, IFRecipe $recipe) {
        if (!$this->exists($key)) $this->set($key, new BlockIFBlock($key));
        $this->get($key)->addRecipe($recipe);
    }

    public function getPositionAsString(Vector3 $block): string {
        return $block->x.",".$block->y.",".$block->z.",".$block->level->getFolderName();
    }
}