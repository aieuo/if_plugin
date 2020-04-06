<?php

namespace aieuo\ip\processes;

interface ProcessIds {
    const COMMAND = 100;
    const SENDMESSAGE = 101;
    const SENDTIP = 102;
    const TELEPORT = 103;
    const BROADCASTMESSAGE = 104;
    const COMMAND_CONSOLE = 105;
    const DO_NOTHING = 106;
    const ADD_ITEM = 107;
    const REMOVE_ITEM = 108;
    const SET_IMMOBILE = 109;
    const UNSET_IMMOBILE = 110;
    const SET_HEALTH = 111;
    const SET_MAXHEALTH = 112;
    const SET_GAMEMODE = 113;
    const SET_NAMETAG = 114;
    const ADD_ENCHANTMENT = 115;
    const ADD_EFFECT = 116;
    const SEND_MESSAGE_TO_OP = 118;
    const SET_SLEEPING = 119;
    const SET_SITTING = 120;
    const ATTACK = 121;
    const KICK = 122;
    const SENDVOICEMESSAGE = 123;
    const SENDTITLE = 124;
    const MOTION = 125;
    const DELAYED_COMMAND = 126;
    const CALCULATION = 127;
    const ADD_VARIABLE = 128;
    const SET_SCALE = 129;
    const EVENT_CANCEL = 130;
    const SET_ITEM = 131;
    const SAVE_DATA = 132;
    const ADD_MONEY = 133;
    const TAKE_MONEY = 134;
    const COOPERATION = 135;
    const DELETE_VARIABLE = 136;
    const SET_BLOCKS = 137;
    const COOPERATION_REPEAT = 138;
    const EXECUTE_OTHER_PLAYER = 139;
    const DELAYED_COMMAND_CONSOLE = 140;
    const SEND_FORM = 141;
    const SET_MONEY = 142;
    const CLEAR_INVENTORY = 143;
    const EQUIP_ARMOR = 144;
    const DELAYED_COOPERATION = 144;
    const SHOW_BOSSBAR = 155;
    const REMOVE_BOSSBAR = 156;
    const GENERATE_RANDOM_NUMBER = 157;
    const SET_FOOD  = 158;
    const ADD_PARTICLE = 159;
    const ADD_SOUND = 160;
    const ADD_PARTICLE_RANGE = 162;
}