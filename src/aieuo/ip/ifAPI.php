<?php

namespace aieuo\ip;

use aieuo\ip\manager\ifManager;

use pocketmine\Server;
use pocketmine\item\Item;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\lang\TranslationContainer;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Entity;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\mcpe\protocol\types\EntityLink;
use pocketmine\event\entity\EntityDamageEvent;

use aieuo\ip\task\DelayedCommandTask;
use aieuo\ip\variable\Variable;

use aieuo\ip\conditions\Condition;
use aieuo\ip\processes\Process;

use aieuo\ip\variable\StringVariable;
use aieuo\ip\variable\NumberVariable;
use aieuo\ip\variable\ListVariable;

class ifAPI {

    private static $sessions = [];

    public function executeIfMatchCondition($player, $datas1, $datas2, $datas3, $args = []){
        $stat = "2";
        foreach($datas1 as $datas){
            $result = ($co = Condition::get($datas["id"]))
                        ->setPlayer($player)
                        ->setValues(
                            $co->parse(
                                ifPlugin::getInstance()
                                  ->getVariableHelper()
                                  ->replaceVariables($datas["content"], $this->getReplaceDatas($args))
                            )
                        )->check();
            if($result === Condition::NOT_FOUND){
                $player->sendMessage("§cエラーが発生しました(id: ".$datas["id"]."が見つかりません)");
                return false;
            }elseif($result === Condition::ERROR){
                return false;
            }elseif($result === Condition::NOT_MATCHED){
                $stat = "3";
            }
        }
        foreach (${"datas".$stat} as $datas) {
            $process = Process::get($datas["id"]);
            if($datas["id"] === Process::EVENT_CANCEL) {
                $process->setValues($args["event"])->execute();
                continue;
            }
            $process->setPlayer($player)
              ->setValues(
                $process->parse(
                    ifPlugin::getInstance()
                      ->getVariableHelper()
                      ->replaceVariables($datas["content"], $this->getReplaceDatas($args))
                )
              )->execute();
        }
        return true;
    }

    public function getReplaceDatas($datas) {
        $player = $datas["player"];
        $server = Server::getInstance();
        $variableHelper = ifPlugin::getInstance()->getVariableHelper();
        $onlines = [];
        foreach ($server->getOnlinePlayers() as $p) {
            $onlines[] = $p->getName();
        }
        $ops = [];
        foreach ($server->getOps()->getAll() as $p => $value) {
            $ops[] = $p;
        }
        $variables = [
            "player" => new StringVariable("player", $player->__toString()),
            "player_name" => new StringVariable("player_name", $player->getName()),
            "nametag" => new StringVariable("nametag", $player->getDisplayName()),
            "player_pos" => new StringVariable("player_pos", $player->x.",".$player->y.",".$player->z.",".$player->level->getFolderName()),
            "player_x" => new NumberVariable("player_x", $player->x),
            "player_y" => new NumberVariable("player_y", $player->y),
            "player_z" => new NumberVariable("player_z", $player->z),
            "player_level" => new StringVariable("player_level", $player->level->getFolderName()),
            "firstplayed" => new NumberVariable("firstplayed", $player->getFirstPlayed()),
            "lastplayed" => new NumberVariable("lastplayed", $player->getLastPlayed()),
            "hand_index" => new NumberVariable("hand_index", $player->getInventory()->getHeldItemIndex()),
            "hand_item" => new StringVariable("hand_item", $player->getInventory()->getItemInHand()->__toString()),
            "hand_name" => new StringVariable("hand_name", $player->getInventory()->getItemInHand()->getName()),
            "hand_id" => new NumberVariable("hand_id", $player->getInventory()->getItemInHand()->getId()),
            "hand_damage" => new NumberVariable("hand_damage", $player->getInventory()->getItemInHand()->getDamage()),
            "hand_count" => new NumberVariable("hand_count", $player->getInventory()->getItemInHand()->getCount()),
            "server_name" => new StringVariable("server_name", $server->getName()),
            "microtime" => new NumberVariable("microtime", microtime(true)),
            "date" => new StringVariable("date", date("m/d h:i:s")),
            "default_level" => new StringVariable("default_level", $server->getDefaultLevel()->getFolderName()),
            "onlines" => new ListVariable("onlines", $onlines),
            "ops" => new ListVariable("ops", $ops)
        ];
        return $variables;
    }

    /**
     * @param  Player $player
     * @return Session
     */
    public static function getSession(Player $player) {
        if(!isset(self::$sessions[$player->getName()])) self::$sessions[$player->getName()] = new Session();
        return self::$sessions[$player->getName()];
    }
}