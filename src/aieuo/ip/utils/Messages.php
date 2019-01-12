<?php

namespace aieuo\ip\utils;

use aieuo\ip\ifPlugin;
use aieuo\ip\conditions\Condition;
use aieuo\ip\processes\Process;

class Messages {

    public static function createMessage($ifs, $matchs, $elses){
    	$mes = "もし\n";
        foreach($ifs as $if){
            $content = Condition::get($if["id"]);
            $content->setValues($content->parse($if["content"]));
            $mes .= $content->getMessage().",\n";
        }
        $mes .= "\nなら\n";
        foreach ($matchs as $match) {
            $process1 = Process::get($match["id"]);
            $process1->setValues($process1->parse($match["content"]));
            $mes .= $process1->getMessage().",\n";
        }
        $mes .= "\n条件に合わなかったら\n";
        foreach ($elses as $else) {
            $process2 = Process::get($else["id"]);
            $process2->setValues($process2->parse($else["content"]));
            $mes .= $process2->getMessage().",\n";
        }
        return $mes;
    }
}