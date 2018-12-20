<?php

namespace aieuo\ip\ifs;

class IfFactory
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
	 * @return IFs | null
	 */
	public static function get($id)
	{
		if(isset(self::$list[$id]))
		{
			return clone self::$list[$id];
		}
		return null;
	}

	/**
	 * @param  IFs $ifs
	 */
	public static function register(IFs $ifs)
	{
		self::$list[$ifs->getId()] = clone $ifs;
	}
}