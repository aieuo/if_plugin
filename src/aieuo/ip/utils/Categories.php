<?php

namespace aieuo\ip\utils;

class Categories {
    // TODO 言語
    const CATEGORY_ACTION_OTHER = 0;
    const CATEGORY_ACTION_SCRIPT = 1;
    const CATEGRY_ACTION_MESSAGE = 2;

    const CATEGORY_CONDITION_OTHER = 1000;
    const CATEGORY_CONDITION_SCRIPT = 1001;

    public static function getActionCategories(): array {
        return [
            self::CATEGORY_ACTION_OTHER => "other",
            self::CATEGORY_ACTION_SCRIPT => "script",
            self::CATEGRY_ACTION_MESSAGE => "message",
        ];
    }

    public static function getConditionCategories(): array {
        return [
            self::CATEGORY_CONDITION_OTHER => "other",
        ];
    }
}