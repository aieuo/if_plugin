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

class ifAPI {

    public function executeIfMatchCondition($player, $datas1, $datas2, $datas3, $args = []){
        $stat = "2";
        foreach($datas1 as $datas){
            $result = ($co = Condition::get($datas["id"]))
                        ->setPlayer($player)
                        ->check();
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
              ->setValues($process->parse($this->replaceVariable($this->replaceDatas($datas["content"], $args))))
              ->execute();
        }
        return true;
    }

    public function replaceVariable($string){
        $count = 0;
        while(preg_match_all("/({[^{}]+})/", $string, $matches)){
            if(++$count >= 10) break;
            foreach ($matches[0] as $name) {
                $val = ifManager::getOwner()->getVariableHelper()->get(substr($name, 1, -1));
                $string = str_replace($name, $val instanceof Variable ? $val->getValue(): $val, $string);
            }
        }
        return $string;
    }

    public function replaceDatas($string, $datas) {
        $player = $datas["player"];
        $server = Server::getInstance();
        $onlines = [];
        foreach ($server->getOnlinePlayers() as $p) {
            $onlines[] = $p->getName();
        }
        $ops = [];
        foreach ($server->getOps() as $p) {
            $ops[] = $p->getName();
        }
        $variables = [
            "{player}" => $player->__toString(),
            "{player_name}" => $player->getName(),
            "{nametag}" => $player->getDisplayName(),
            "{player_pos}" => $player->x.",".$player->y.",".$player->z.",".$player->level->getFolderName(),
            "{player_x}" => $player->x,
            "{player_y}" => $player->y,
            "{player_z}" => $player->z,
            "{player_level}" => $player->level->getFolderName(),
            "{player_firstplayed}" => $player->getFirstPlayed(),
            "{player_lastplayed}" => $player->getLastPlayed(),
            "{player_lastplayed}" => $player->getLastPlayed(),
            "{hand_index}" => $player->getInventory()->getHeldItemIndex(),
            "{hand_item}" => $player->getInventory()->getItemInHand()->__toString(),
            "{hand_id}" => $player->getInventory()->getItemInHand()->getId(),
            "{hand_damage}" => $player->getInventory()->getItemInHand()->getDamage(),
            "{server_name}" => $server->getName(),
            "{server_tick}" => $server->getTick(),
            "{default_level}" => $server->getDefaultLevel()->getFolderName(),
            "{online_players}" => implode(",", $onlines),
            "{ops}" => implode(",", $ops),
        ];
        foreach ($variables as $key => $value) {
            $string = str_replace($key, $value, $string);
        }
        return $string;
    }
}