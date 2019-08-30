<?php

namespace aieuo\ip\action\script;

class ScriptFactory {
    private static $list = [];

    public static function init() {
        self::register(new IfScript());
        self::register(new AndScript());
        self::register(new ORScript());
    }

    /**
     * @param  string $id
     * @return Script|null
     */
    public static function get(string $id): ?Script {
        if (isset(self::$list[$id])) {
            return clone self::$list[$id];
        }
        return null;
    }

    public static function getAll(): array {
        return self::$list;
    }

    /**
     * @param  Script $script
     */
    public static function register(Script $script) {
        self::$list[$script->getId()] = clone $script;
    }
}