<?php

namespace aieuo\ip;

use pocketmine\event\Event;
use pocketmine\Player;
use pocketmine\Server;

use aieuo\ip\variable\StringVariable;
use aieuo\ip\variable\NumberVariable;
use aieuo\ip\variable\ListVariable;
use aieuo\ip\utils\Language;
use aieuo\ip\processes\Process;
use aieuo\ip\conditions\Condition;

class IFAPI {

    public function checkCondition($player, $data, $options = []) {
        $matched = true;
        foreach ($data as $value) {
            $result = ($co = Condition::get($value["id"]))
                        ->setPlayer($player)
                        ->setValues(
                            $co->parse(
                                str_replace("\\n", "\n", IFPlugin::getInstance()
                                  ->getVariableHelper()
                                  ->replaceVariables($value["content"], $this->getReplaceData($options)))
                            )
                        )->check();
            if ($result === Condition::ERROR or $result === Condition::NOT_FOUND) {
                return $result;
            } elseif ($result === Condition::NOT_MATCHED) {
                $matched = false;
            }
        }
        return $matched ? Condition::MATCHED : Condition::NOT_MATCHED;
    }

    public function executeProcess($player, $ifData, $options) {
        $replaceData = $this->getReplaceData($options);
        foreach ($ifData as $data) {
            $process = Process::get($data["id"]);
            $process->replaceDatas = $replaceData;
            if (isset($options["event"]) and $options["event"] instanceof Event) $process->setEvent($options["event"]);
            if ($data["id"] === Process::EVENT_CANCEL) {
                $process->setValues($options["event"])->execute();
                continue;
            }
            $process->setPlayer($player)
                ->setValues(
                    $process->parse(
                        str_replace("\\n", "\n", IFPlugin::getInstance()
                        ->getVariableHelper()
                        ->replaceVariables($data["content"], $replaceData))
                    )
                )->execute();
        }
    }

    public function executeIfMatchCondition(Player $player, $data1, $data2, $data3, $options = []) {
        $match = $this->checkCondition($player, $data1, $options);
        switch ($match) {
            case Condition::MATCHED:
                $data = $data2;
                break;
            case Condition::NOT_MATCHED:
                $data = $data3;
                break;
            case Condition::NOT_FOUND:
                $player->sendMessage(Language::get("if.contents.notFound", [$data1["id"]]));
                return false;
            case Condition::ERROR:
            default:
                return false;
        }
        $this->executeProcess($player, $data, $options);
        return true;
    }

    public function getReplaceData($data) {
        /** @var Player $player */
        $player = $data["player"];
        $server = Server::getInstance();
        $onlines = [];
        foreach ($server->getOnlinePlayers() as $p) {
            $onlines[] = $p->getName();
        }
        $ops = [];
        foreach ($server->getOps()->getAll() as $p => $value) {
            $ops[] = $p;
        }
        return [
            "player" => new StringVariable("player", $player->__toString()),
            "player_name" => new StringVariable("player_name", $player->getName()),
            "nametag" => new StringVariable("nametag", $player->getDisplayName()),
            "player_pos" => new StringVariable("player_pos", $player->x.",".$player->y.",".$player->z.",".$player->level->getFolderName()),
            "player_x" => new NumberVariable("player_x", $player->x),
            "player_y" => new NumberVariable("player_y", $player->y),
            "player_z" => new NumberVariable("player_z", $player->z),
            "player_level" => new StringVariable("player_level", $player->level->getFolderName()),
            "health" => new NumberVariable("health", $player->getHealth()),
            "max_health" => new NumberVariable("max_health", $player->getMaxHealth()),
            "firstplayed" => new NumberVariable("firstplayed", $player->getFirstPlayed()),
            "lastplayed" => new NumberVariable("lastplayed", $player->getLastPlayed()),
            "hand_index" => new NumberVariable("hand_index", $player->getInventory()->getHeldItemIndex()),
            "hand_item" => new StringVariable("hand_item", $player->getInventory()->getItemInHand()->__toString()),
            "hand_name" => new StringVariable("hand_name", $player->getInventory()->getItemInHand()->getName()),
            "hand_id" => new NumberVariable("hand_id", $player->getInventory()->getItemInHand()->getId()),
            "hand_lore" => new ListVariable("hand_lore", $player->getInventory()->getItemInHand()->getLore()),
            "hand_damage" => new NumberVariable("hand_damage", $player->getInventory()->getItemInHand()->getDamage()),
            "hand_count" => new NumberVariable("hand_count", $player->getInventory()->getItemInHand()->getCount()),
            "server_name" => new StringVariable("server_name", $server->getName()),
            "microtime" => new NumberVariable("microtime", microtime(true)),
            "time" => new StringVariable("time", date("h:i:s")),
            "date" => new StringVariable("date", date("m/d")),
            "default_level" => new StringVariable("default_level", $server->getDefaultLevel()->getFolderName()),
            "onlines" => new ListVariable("onlines", $onlines),
            "ops" => new ListVariable("ops", $ops)
        ];
    }

    public static function createIFMessage(array $ifs, array $matchs, array $others): string {
        $mes = Language::get("message.if")."\n";
        foreach ($ifs as $if) {
            $content = Condition::get($if["id"]);
            $content->setValues($content->parse($if["content"]));
            $mes .= $content->getDetail() === false ?
                        $content->getDescription()."§f,\n":
                        $content->getDetail()."§f,\n";
        }
        $mes .= "\n".Language::get("message.match")."\n";
        foreach ($matchs as $match) {
            $process1 = Process::get($match["id"]);
            $process1->setValues($process1->parse($match["content"]));
            $mes .= $process1->getDetail() === false ?
                        $process1->getDescription()."§f,\n":
                        $process1->getDetail()."§f,\n";
        }
        $mes .= "\n".Language::get("message.other")."\n";
        foreach ($others as $other) {
            $process2 = Process::get($other["id"]);
            $process2->setValues($process2->parse($other["content"]));
            $mes .= $process2->getDetail() === false ?
                        $process2->getDescription()."§f,\n":
                        $process2->getDetail()."§f,\n";
        }
        return $mes;
    }
}