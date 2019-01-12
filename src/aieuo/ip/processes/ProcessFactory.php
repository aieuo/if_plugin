<?php

namespace aieuo\ip\processes;

class ProcessFactory
{
	private static $list = [];

	public static function init()
	{
		self::register(new DoNothing());
		self::register(new SendMessage());
		self::register(new SendTip());
		self::register(new SendTitle());
		self::register(new BroadcastMessage());
		self::register(new SendMessageToOp());
		self::register(new SendVoiceMessage());
		self::register(new Command());
		self::register(new CommandConsole());
		self::register(new DelayedCommand());
		self::register(new Teleport());
		self::register(new Motion());
		self::register(new Calculation());
		self::register(new AddVariable());
		self::register(new AddItem());
		self::register(new RemoveItem());
		self::register(new SetImmobile());
		self::register(new UnSetImmobile());
		self::register(new AddEnchantment());
		self::register(new AddEffect());
		self::register(new SetNametag());
		self::register(new SetSleeping());
		self::register(new SetSitting());
		self::register(new SetGamemode());
		self::register(new SetHealth());
		self::register(new SetMaxHealth());
		self::register(new Attack());
		self::register(new Kick());
		self::register(new EventCancel());
		self::register(new setItem());
	}

	/**
	 * @param  int $id
	 * @return Process
	 */
	public static function get($id)
	{
		if(isset(self::$list[$id]))
		{
			return clone self::$list[$id];
		}
		return new Process();
	}

	public static function getAll()
	{
		return self::$list;
	}

	/**
	 * @param  Condition $process
	 */
	public static function register(Process $process)
	{
		self::$list[$process->getId()] = clone $process;
	}
}