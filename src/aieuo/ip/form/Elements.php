<?php

namespace aieuo\ip\form;

class Elements {
    public static function getLabel($text){
        return [
            "type" => "label",
            "text" => (string)$text
        ];
    }

    public static function getInput($text, $placeholder = "", $default = ""){
        return [
            "type" => "input",
            "text" => (string)$text,
            "default" => (string)$default,
            "placeholder" => (string)$placeholder
        ];
    }

    public static function getToggle($text, $default = false){
        return [
            "type" => "toggle",
            "text" => (string)$text,
            "default" => $default,
        ];
    }

    public static function getDropdown($text, $options = [], $default = 0){
        return [
            "type" => "dropdown",
            "text" => (string)$text,
            "options" => $options,
            "default" => $default
        ];
    }

    public static function getSlider($text, $min = 0, $max = 0, $default = 0, $step = 1) {
        return [
            "type" => "slider",
            "text" => (string)$text,
            "min" => (int)$min,
            "max" => (int)$max,
            "default" => (int)$default,
            "step" => (int)$step
        ];
    }

    public static function getButton($text){
        return [
            "text" => (string)$text
        ];
    }
}