<?php

namespace aieuo\ip\utils;

use aieuo\ip\conditions\Condition;
use aieuo\ip\processes\Process;

use aieuo\ip\utils\Language;

class Messages {

    public static function createMessage($ifs, $matchs, $elses){
    	$mes = Language::get("message.if")."\n";
        foreach($ifs as $if){
            $content = Condition::get($if["id"]);
            $content->setValues($content->parse($if["content"]));
            $mes .= $content->getMessage() === false ?
                        $content->getDescription()."§f,\n":
                        $content->getMessage()."§f,\n";
        }
        $mes .= "\nなら\n";
        foreach ($matchs as $match) {
            $process1 = Process::get($match["id"]);
            $process1->setValues($process1->parse($match["content"]));
            $mes .= $process1->getMessage() === false ?
                        $process1->getDescription()."§f,\n":
                        $process1->getMessage()."§f,\n";
        }
        $mes .= "\n条件に合わなかったら\n";
        foreach ($elses as $else) {
            $process2 = Process::get($else["id"]);
            $process2->setValues($process2->parse($else["content"]));
            $mes .= $process2->getMessage() === false ?
                        $process2->getDescription()."§f,\n":
                        $process2->getMessage()."§f,\n";
        }
        return $mes;
    }
}