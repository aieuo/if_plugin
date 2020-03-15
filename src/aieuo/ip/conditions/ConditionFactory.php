<?php

namespace aieuo\ip\conditions;

use aieuo\ip\economy\EconomyLoader;
use aieuo\ip\IFPlugin;

class ConditionFactory {
    private static $list = [];

    public static function init() {
        $existsEconomy = IFPlugin::getInstance()->getEconomy() instanceof EconomyLoader;

        self::register(new NoCheck());
        if ($existsEconomy) self::register(new TakeMoney());
        if ($existsEconomy) self::register(new OverMoney());
        if ($existsEconomy) self::register(new LessMoney());
        self::register(new InHand());
        self::register(new ExistsItem());
        self::register(new RemoveItem());
        self::register(new CanAddItem());
        self::register(new IsOp());
        self::register(new IsSneaking());
        self::register(new IsFlying());
        self::register(new GameMode());
        self::register(new InArea());
        self::register(new InAreaWithAxis());
        self::register(new RandomNumber());
        self::register(new Comparison());
        self::register(new ExistsVariable());
    }
    /**
     * @param  int $id
     * @return Condition|null
     */
    public static function get($id): ?Condition {
        if (isset(self::$list[$id])) {
            return clone self::$list[$id];
        }
        return null;
    }

    public static function getAll(): array {
        return self::$list;
    }

    /**
     * @param  Condition $condition
     */
    public static function register(Condition $condition) {
        self::$list[$condition->getId()] = clone $condition;
    }
}