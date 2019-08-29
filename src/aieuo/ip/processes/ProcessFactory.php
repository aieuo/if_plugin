<?php

namespace aieuo\ip\processes;

class ProcessFactory {
    private static $list = [];

    public static function init() {
        self::register(new DoNothing());
        self::register(new SendMessage());
        self::register(new SendTip());
        self::register(new SendTitle());
        self::register(new BroadcastMessage());
        self::register(new SendMessageToOp());
        self::register(new SendVoiceMessage());
        self::register(new SendForm());
        self::register(new Command());
        self::register(new CommandConsole());
        self::register(new DelayedCommand());
        self::register(new DelayedCommandConsole());
        self::register(new AddMoney());
        self::register(new TakeMoney());
        self::register(new SetMoney());
        self::register(new Teleport());
        self::register(new Motion());
        self::register(new SetScale());
        self::register(new Calculation());
        self::register(new AddVariable());
        self::register(new DeleteVariable());
        self::register(new AddItem());
        self::register(new RemoveItem());
        self::register(new SetItem());
        self::register(new ClearInventory());
        self::register(new SetImmobile());
        self::register(new UnSetImmobile());
        self::register(new EquipArmor());
        self::register(new AddEnchantment());
        self::register(new AddEffect());
        self::register(new SetNametag());
        self::register(new SetSleeping());
        self::register(new SetSitting());
        self::register(new EventCancel());
        self::register(new SetGamemode());
        self::register(new Cooperation());
        self::register(new CooperationRepeat());
        self::register(new DelayedCooperation());
        self::register(new ExecuteOtherPlayer());
        self::register(new SetBlocks());
        self::register(new SaveDatas());
        self::register(new SetHealth());
        self::register(new SetMaxHealth());
        self::register(new Attack());
        self::register(new Kick());
    }

    /**
     * @param  int $id
     * @return Process|null
     */
    public static function get($id): ?Process {
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