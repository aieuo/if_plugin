<?php

namespace aieuo\ip\action\process;

class ProcessFactory {
    private static $list = [];

    public static function init() {
        self::register(new SendMessage());
    }

    /**
     * @param  string $id
     * @return Process|null
     */
    public static function get(string $id): ?Process {
        if (isset(self::$list[$id])) {
            return clone self::$list[$id];
        }
        return null;
    }

    public static function getAll(): array {
        return self::$list;
    }

    /**
     * @param  Process $process
     */
    public static function register(Process $process) {
        self::$list[$process->getId()] = clone $process;
    }
}