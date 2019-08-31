<?php

namespace aieuo\ip\action\process;

interface ProcessNames {
    const DO_NOTHING = "doNothing";
    const SEND_MESSAGE = "sendMessage";
    const SEND_TIP = "sendTip";
    const SEND_POPUP = "sendPopup";
    const SEND_TITLE = "sendTitle";
    const SEND_BROADCAST_MESSAGE = "broadcastMessage";
    const SEND_MESSAGE_TO_OP = "sendMessageToOp";
}