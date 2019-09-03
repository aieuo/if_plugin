<?php

namespace aieuo\ip\recipe;

class BlockIFBlock extends IFBlock {
    public function jsonSerialize(): array {
        $recipes = [];
        foreach ($this->getAllRecipe() as $recipe) {
            $name = $recipe->getName();
            if ($name === null) {
                $recipes[] = $recipe;
            } else {
                $recipes[$name] = $recipe;
            }
        }
        return [
            "key" => $this->name,
            "recipes" => $recipes,
        ];
    }
}