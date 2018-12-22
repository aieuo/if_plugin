<?php

namespace aieuo\ip\conditions;

class ConditionFactory
{
	private static $list = [];

	public static function init()
	{
		self::register(new NoCheck());
		self::register(new TakeMoney());
		self::register(new OverMoney());
		self::register(new InHand());
		self::register(new ExistsItem());
		self::register(new RemoveItem());
		self::register(new IsOp());
		self::register(new IsSneaking());
		self::register(new IsFlying());
		self::register(new Gamemode());
		self::register(new InArea());
		self::register(new RandomNumber());
		self::register(new Comparison());
		self::register(new ExistsVariable());
	}
	/**
	 * @param  int $id
	 * @return Condition | null
	 */
	public static function get($id)
	{
		if(isset(self::$list[$id]))
		{
			return clone self::$list[$id];
		}
		return new Condition();
	}

	/**
	 * @param  Condition $condition
	 */
	public static function register(Condition $condition)
	{
		self::$list[$condition->getId()] = clone $condition;
	}
}