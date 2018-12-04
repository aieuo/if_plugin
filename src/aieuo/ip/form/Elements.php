<?php

namespace aieuo\ip\form;

class Elements {
    public static function getLabel($text){
        return [
            "type" => "label",
            "text" => $text
        ];
    }

    public static function getInput($text, $placeholder = "", $default = ""){
        return [
            "type" => "input",
            "text" => $text,
            "default" => $default,
            "placeholder" => $placeholder
        ];
    }

    public static function getToggle($text){
        return [
            "type" => "toggle",
            "text" => $text
        ];
    }

    public static function getDropdown($text, $options, $default = 0){
    	return [
            "type" => "dropdown",
            "text" => $text,
            "options" => $options,
            "defaultOptionIndex" => $default
        ];
    }

    public static function getButton($text){
    	return [
    		"text" => $text
    	];
    }
}