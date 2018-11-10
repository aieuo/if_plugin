<?php

namespace aieuo\ip\utils;

use aieuo\ip\ifPlugin;

class Messages {

    public static function createMessage($ifs, $matchs, $elses){
    	$mes = "もし\n";
        foreach($ifs as $if){
            $mes .= self::getMessage($if["id"], $if["content"]).",\n";
        }
        $mes .= "\nなら\n";
        foreach ($matchs as $match) {
            $mes .= self::getMessage($match["id"], $match["content"]).",\n";
        }
        $mes .= "\n条件に合わなかったら\n";
        foreach ($elses as $else) {
            $mes .= self::getMessage($else["id"], $else["content"]).",\n";
        }
        return $mes;
    }

	public static function getMessage($num, $value = "", $event = false){
        switch ($num){
            case ifPlugin::IF_NO_CHECK:
                $message = "何も確認しない";
                break;
            case ifPlugin::IF_TAKEMONEY:
                $message = "$".$value."払える";
                break;
            case ifPlugin::IF_OVERMONEY:
                $message = "$".$value."より所持金が多い";
                break;
            case ifPlugin::IF_HAVEINGITEM:
                $message = "idが".$value."のアイテムを手に持ってる";
                break;
            case ifPlugin::IF_EXISTITEM:
                $message = "idが".$value."アイテムがインベントリに入っている";
                break;
            case ifPlugin::IF_REMOVEITEM:
                $message = "インベントリから(".$value.")を削除できる";
                break;
            case ifPlugin::IF_IS_OP:
                $message = "プレイヤーがop";
                break;
            case ifPlugin::IF_IS_SNEAKING:
                $message = "プレイヤーがスニークしている";
                break;
            case ifPlugin::IF_IS_FLYING:
                $message = "プレイヤーが飛んでいる";
                break;
            case ifPlugin::IF_GAMEMODE:
                $message = "ゲームモードが(".$value.")";
                break;
            case ifPlugin::IF_IN_AREA:
                preg_match("/([xyz]\(-?[0-9\.]+,-?[0-9\.]+\))+/", $value, $matches);
                array_shift($matches);
                $mes = [];
                foreach ($matches as $match) {
                    if(!preg_match("/([xyz])\((-?[0-9\.]+),-?([0-9\.]+)\)/", $match, $result))continue;
                    $min = min((float)$result[2], (float)$result[3]);
                    $max = max((float)$result[2], (float)$result[3]);
                    $mes[] = $result[1]."座標が".$min."以上、".$max."以下";
                }
                if(count($mes) == 0){
                    $message = "§c[指定した範囲内にいたら] 正しく入力できていません§f";
                }else{
                    $message = "プレイヤーが".implode(",", $mes)."にいる";
                }
                break;
            case ifPlugin::IF_RANDOM_NUMBER:
                if(!preg_match("/(-?[0-9]+),(-?[0-9]+);(-?[0-9]+)/", $value, $matches)){
                    $message = "§c[乱数が指定したものだったら] 正しく入力できていません§f";
                }else{
                    $min = min((int)$matches[1], (int)$matches[2]);
                    $max = max((int)$matches[1], (int)$matches[2]);
                    $message = $min."～".$max."の範囲の乱数が".$matches[3];
                }
                break;
            case ifPlugin::IF_COMPARISON:
                if(!preg_match("/([^!>=<]+)([!>=<]{1,2})([^!>=<]+)/", $value, $matches)){
                    $message = "§c[二つの値を比較する] 正しく入力できていません§f";
                }else{
                    $operator = $matches[2];
                    $val1 = trim(rtrim($matches[1]));
                    $val2 = trim(rtrim($matches[3]));
                    switch ($operator){
                        case "=":
                        case "==":
                            $message = $val1."と".$val2."が等しい";
                            break;
                        case "!=":
                        case "=!":
                            $message = $val1."と".$val2."が等しくない";
                            break;
                        case ">":
                            $message = $val1."が".$val2."より大きい";
                            break;
                        case "<":
                            $message = $val1."が".$val2."より小さい";
                            break;
                        case ">=":
                        case "=>":
                            $message = $val1."が".$val2."以上";
                            break;
                        case "<=":
                        case "=<":
                            $message = $val1."が".$val2."以下";
                            break;
                        case "><":
                            $message = $val1."の中に".$val2."が含まれている";
                            break;
                        case "<>":
                            $message = $val1."の中に".$val2."が含まれていない";
                            break;
                        default:
                            $message = "§cその組み合わせは使用できません 次の中から選んでください[==|>|>=|<|<=|!=]§r";
                            break;
                    }
                }
                break;
            case ifPlugin::DO_NOTHING:
                $message = "何もしない";
                break;
            case ifPlugin::SENDMESSAGE:
                $message = "チャット欄に(".$value.")を送信する";
                break;
            case ifPlugin::SENDTIP:
                $message = "tip欄に(".$value.")を送信する";
                break;
            case ifPlugin::SENDTITLE:
                $message = "タイトル欄に(".$value.")を送信する";
                break;
            case ifPlugin::BROADCASTMESSAGE:
                $message = "全員のチャット欄に(".$value.")を送信する";
                break;
            case ifPlugin::SENDMESSAGE_TO_OP:
                $message = "opだけに(".$value.")を送信する";
                break;
            case ifPlugin::SENDVOICEMESSAGE:
                $message = "音声付きのメッセージ(".$value.")を送信する";
                break;
            case ifPlugin::COMMAND:
                $message = "コマンド(/".$value.")を実行する";
                break;
            case ifPlugin::COMMAND_CONSOLE:
                $message = "コンソールからコマンド(/".$value.")を実行する";
                break;
            case ifPlugin::DELAYED_COMMAND:
                if(!preg_match("/([0-9]+),(.+)/", $value, $matches)){
                    $player->sendMessage("§c[遅れてコマンド実行] 書き方が正しくありません§f");
                    break;
                }
                $message = $matches[1]."秒後に/".$matches[2]."を実行する";
                break;
            case ifPlugin::TELEPORT:
                $pos = explode(",", $value);
                if(!isset($pos[1]))$pos[1] = 0;
                if(!isset($pos[2]))$pos[2] = 0;
                $message = "(".implode(",", $pos).")にテレポートする";
                break;
            case ifPlugin::MOTION:
                $pos = explode(",", $value);
                if(!isset($pos[1]))$pos[1] = 0;
                if(!isset($pos[2]))$pos[2] = 0;
                $message = "(".implode(",", $pos).")ブロック分動かす";
                break;
            case ifPlugin::CALCULATION:
                if(!preg_match("/([^+＋-ー*\/%％×÷]+)([+＋-ー*\/%×÷])([^+＋-ー*\/%×÷]+)/", $value, $matches)){
                    $message = "§c[計算する] 正しく入力できていません§f";
                }else{
                    $operator = $matches[2];
                    $val1 = trim(rtrim($matches[1]));
                    $val2 = trim(rtrim($matches[3]));
                    switch ($operator){
                        case "+":
                        case "＋":
                            $message = $val1."と".$val2."を足す";
                            break;
                        case "-":
                        case "ー":
                            $message = $val1."から".$val2."を引く";
                            break;
                        case "*":
                        case "×":
                            $message = $val1."と".$val2."を掛ける";
                            break;
                        case "/":
                        case "÷":
                            $message = $val1."を".$val2."で割る";
                            break;
                        case "%":
                            $message = $val1."を".$val2."で割った余り";
                            break;
                        default:
                            $message = "§cその組み合わせは使用できません 次の中から選んでください[+|-|*|/|%]§r";
                            break;
                    }
                }
                break;
            case ifPlugin::ADD_VARIABLE:
                $datas = explode(",", $value);
                if(!isset($datas[1])){
                    $message = "§c[変数を追加する] 正しく入力できていません§f";
                    break;
                }
                $message = $datas[0]."という名前の変数(".$datas[1].")を追加する";
                break;
            case ifPlugin::ADD_ITEM:
                $ids = explode(":", $value);
                if(!isset($ids[1]))$ids[1] = 0;
                if(!isset($ids[2]))$ids[2] = 1;
                if(!isset($ids[3]))$ids[3] = "";
                $message = "インベントリに($ids[0]:$ids[1] x$ids[2], $ids[3])を追加する";
                break;
            case ifPlugin::REMOVE_ITEM:
                $ids = explode(":", $value);
                if(!isset($ids[1]))$ids[1] == 0;
                if(!isset($ids[2]))$ids[2] == "全て";
                $message = "インベントリから($ids[0]:$ids[1] x$ids[2])を削除する";
                break;
            case ifPlugin::SET_IMMOBILE:
                $message = "プレイヤーを動けなくする";
                break;
            case ifPlugin::UNSET_IMMOBILE:
                $message = "プレイヤーを動けるようにする";
                break;
            case ifPlugin::ADD_ENCHANTMENT:
                $args = explode(",", $value);
                if(!isset($args[1]) or (int)$args[1] <= 0)$args[1] = 1;
                $message = "手に持ってるアイテムに(id:".$args[0].",レベル:".$args[1].")のエンチャントを追加する";
                break;
            case ifPlugin::ADD_EFFECT:
                $args = explode(",", $value);
                if(!isset($args[1]) or (int)$args[1] <= 0)$args[1] = 1;
                if(!isset($args[2]) or (int)$args[2] <= 0)$args[2] = 30;
                $time = (int)$args[2] * 20;
                $message = "プレイヤーに(id:".$args[0].",強さ:".$args[1].")のエフェクトを".$time."秒間与える";
                break;
            case ifPlugin::SET_NAMETAG:
                $message = "表示している名前を(".$value.")に変更する";
                break;
            case ifPlugin::SET_SLEEPING:
                $pos = explode(",", $value);
                if(!isset($pos[1]))$pos[1] = 0;
                if(!isset($pos[2]))$pos[2] = 0;
                $message = "(".implode(",", $pos).")で寝かせる";
                break;
            case ifPlugin::SET_SITTING:
                $pos = explode(",", $value);
                if(!isset($pos[1]))$pos[1] = 0;
                if(!isset($pos[2]))$pos[2] = 0;
                $message = "(".implode(",", $pos).")で座らせる";
                break;
            case ifPlugin::SET_GAMEMODE:
                $message = "ゲームモードを(".$value.")に設定する";
                break;
            case ifPlugin::SET_HEALTH:
                $message = "体力を(".$value.")に設定する";
                break;
            case ifPlugin::SET_MAXHEALTH:
                $message = "最大体力を(".$value.")に設定する";
                break;
            case ifPlugin::ATTACK:
                $message = "(".$value.")ダメージ与える";
                break;
            case ifPlugin::KICK:
                $message = "(".$value.")でキックする";
                break;
            case ifPlugin::EVENT_CANCELL:
                $message = "イベントをキャンセルする";
                break;
            default:
            	$message = "";
            	break;
        }
        return $message;
    }

    public static function getFormMessage($num){
        switch ($num){
            case ifPlugin::IF_TAKEMONEY:
                $message = "もし\n§lお金を";
                $next = "§l払えるなら\n\n";
                $placeholder = "金額を入力してください";
                break;
            case ifPlugin::IF_OVERMONEY:
                $message = "もし\n§l所持金が";
                $next = "§lより多いなら\n\n";
                $placeholder = "金額を入力してください";
                break;
            case ifPlugin::IF_HAVEINGITEM:
                $message = "もし\n§lidが\n§r(id:meta:数 のように石が10個なら1:0:10, 数を考えないなら1:0)";
                $next = "§lのアイテムを手に持ってるなら\n\n";
                $placeholder = "上のカッコ内のようにidを入力してください";
                break;
            case ifPlugin::IF_EXISTITEM:
                $message = "もし\n§lidが\n§r(id:meta:数 のように安山岩が3個なら1:5:3, 1:5だけなら数は1個になります)";
                $next = "§lのアイテムがインベントリにあるなら\n\n";
                $placeholder = "上のカッコ内のようにidを入力してください";
                break;
            case ifPlugin::IF_REMOVEITEM:
                $message = "もし\n§lインベントリからidが\n§r(id:meta:数 のように黄色の羊毛が2個なら35:4:2 , 35:4だけなら持ってる数すべて)";
                $next = "§lのアイテムを削除できるなら\n\n";
                $placeholder = "上のカッコ内のようにidを入力してください";
                break;
            case ifPlugin::IF_GAMEMODE:
                $message = "もし\n§lゲームモードが\n§r(0,1,2,3,s,c,a,v)";
                $next = "§lなら\n\n";
                $placeholder = "カッコ内から一つ選んで入力してください";
                break;
            case ifPlugin::IF_IN_AREA:
                $message = "もし\n§lプレイヤーが\n§r(書き方: <x|y|z>(<最小の座標>,<最大の座標>)\nx座標が0~10の範囲なら:  x(0,10)\nx,y,z座標が0~5の範囲なら:  x(0,5)y(0,5)z(0,5))";
                $next = "§lの範囲にいたら\n\n";
                $placeholder = "カッコ内のように入力してください";
                break;
            case ifPlugin::IF_RANDOM_NUMBER:
                $message = "もし\n§l乱数\n§r(最小値,最大値;確認する数)\n1~10の範囲の乱数が2の時に処理するなら:  1,10;2";
                $next = "§lなら\n\n";
                $placeholder = "カッコ内のように入力してください";
            case ifPlugin::IF_COMPARISON:
                $message = "もし\n§l二つの値を比較して条件を満たしている\n§r(値1 [== | > | < | >= | <= | !=])\nイベントのブロックを触ったときにブロックのidが1か調べるなら:  {blockid}==1";
                $next = "§lなら\n\n";
                $placeholder = "カッコ内のように入力してください";
                break;
            case ifPlugin::SENDMESSAGE:
                $message = "§lチャット欄に";
                $next = "§lを送信する\n\n";
                $placeholder = "送信するメッセージを入力してください";
                break;
            case ifPlugin::SENDTIP:
                $message = "§ltip欄に";
                $next = "§lを送信する\n\n";
                $placeholder = "送信するメッセージを入力してください";
                break;
            case ifPlugin::SENDTITLE:
                $message = "§ltitle欄に";
                $next = "§lを送信する\n\n";
                $placeholder = "送信するメッセージを入力してください";
                break;
            case ifPlugin::BROADCASTMESSAGE:
                $message = "§l全員のチャット欄に";
                $next = "§lを送信する\n\n";
                $placeholder = "送信するメッセージを入力してください";
                break;
            case ifPlugin::SENDMESSAGE_TO_OP:
                $message = "§lopだけに";
                $next = "§lを送信する\n\n";
                $placeholder = "送信するメッセージを入力してください";
                break;
            case ifPlugin::SENDVOICEMESSAGE:
                $message = "§lチャット欄に音声付きの";
                $next = "§lを送信する\n\n";
                $placeholder = "送信するメッセージを入力してください";
                break;
            case ifPlugin::COMMAND:
                $message = "§lコマンド";
                $next = "§lを実行する\n\n";
                $placeholder = "コマンドを最初の/を外して入力してください";
                break;
            case ifPlugin::COMMAND_CONSOLE:
                $message = "§lコンソールからコマンド";
                $next = "§lを実行する\n\n";
                $placeholder = "コマンドを最初の/を外して入力してください";
                break;
            case ifPlugin::DELAYED_COMMAND:
                $message = "§l?秒後にコマンド\n§r(秒数, コマンド)\n10秒後に /aieuo を実行するなら: 10,aieuo";
                $next = "§lを実行する\n\n";
                $placeholder = "カッコ内のように入力してください";
                break;
            case ifPlugin::TELEPORT:
                $message = "(0,0,0,world  のように,で区切って)";
                $next = "§lにテレポートする\n\n";
                $placeholder = "上のカッコ内のように座標を入力してください";
                break;
            case ifPlugin::MOTION:
                $message = "(0,1,0  のように,で区切って)";
                $next = "§lにテレポートする\n\n";
                $placeholder = "上のカッコ内のように座標を入力してください";
                break;
            case ifPlugin::CALCULATION:
                $message = "§l二つの値を計算する\n§r(+ | - | * | / | %)";
                $next = "§l\n\n";
                $placeholder = "カッコ内の演算子を使用できます";
                break;
            case ifPlugin::ADD_VARIABLE:
                $message = "§l変数\n§r(名前,値  \n  aieuoという名前で値が100の変数を追加するなら:   aieuo,100)";
                $next = "§lを追加する\n\n";
                $placeholder = "上のカッコ内のようにidを入力してください";
                break;
            case ifPlugin::ADD_ITEM:
                $message = "§lインベントリにidが\n§r(id:meta:数 ,名前を付けるならid:meta:数:名前 のように)";
                $next = "§lのアイテムを追加する\n\n";
                $placeholder = "上のカッコ内のようにidを入力してください";
                break;
            case ifPlugin::REMOVE_ITEM:
                $message = "§lインベントリからidが\n§r(id:meta:数 数を入れなかったら持ってる数すべて)";
                $next = "§lのアイテムを削除する\n\n";
                $placeholder = "上のカッコ内のようにidを入力してください";
                break;
            case ifPlugin::ADD_ENCHANTMENT:
                $message = "§lid,レベルが\n§r(,で区切って)";
                $next = "§lのエンチャントを手に持ってるアイテムに追加する\n\n";
                $placeholder = "数値を入力してください";
                break;
            case ifPlugin::ADD_EFFECT:
                $message = "§lid,強さ,時間が\n§r(,で区切って)";
                $next = "§lのエフェクトを与える\n\n";
                $placeholder = "数値を入力してください";
                break;
            case ifPlugin::SET_NAMETAG:
                $message = "§l表示している名前を";
                $next = "§lにする\n\n";
                $placeholder = "かっこの中から一つ選んで入力してください";
                break;
            case ifPlugin::SET_SLEEPING:
                $message = "(0,0,0,world  のように,で区切って)";
                $next = "§lで寝かせる\n\n";
                $placeholder = "上のカッコ内のように座標を入力してください";
                break;
            case ifPlugin::SET_SITTING:
                $message = "(0,0,0,world  のように,で区切って)";
                $next = "§lで座らせる\n\n";
                $placeholder = "上のカッコ内のように座標を入力してください";
                break;
            case ifPlugin::SET_GAMEMODE:
                $message = "§lゲームモードを\n§r(0,1,2,3,s,c,a,v)";
                $next = "§lにする\n\n";
                $placeholder = "かっこの中から一つ選んで入力してください";
                break;
            case ifPlugin::SET_HEALTH:
                $message = "§l体力を\n§r(2でハート一個分)";
                $next = "§lにする\n\n";
                $placeholder = "数値を入力してください";
                break;
            case ifPlugin::SET_MAXHEALTH:
                $message = "§l最大体力を\n§r(2でハート一個分)";
                $next = "§lにする\n\n";
                $placeholder = "数値を入力してください";
                break;
            case ifPlugin::ATTACK:
                $message = "§l\n§r(2でハート一個分)";
                $next = "§lダメージ与える\n\n";
                $placeholder = "数値を入力してください";
                break;
            case ifPlugin::KICK:
                $message = "";
                $next = "§lでキックする\n\n";
                $placeholder = "理由を入力してください";
                break;
        }
        if(isset($message))return [
        	"type" => "input",
            "message" => $message,
            "next" => $next,
            "placeholder" => $placeholder
        ];
        switch ($num){
            case ifPlugin::IF_NO_CHECK:
                $message = "何も確認しない";
                break;
            case ifPlugin::IF_IS_OP:
                $message = "もし\nOPなら";
                break;
            case ifPlugin::IF_IS_SNEAKING:
                $message = "もし\nスニークしているなら";
                break;
            case ifPlugin::IF_IS_SNEAKING:
                $message = "もし\n飛んでいるなら";
                break;
            case ifPlugin::DO_NOTHING:
                $message = "何もしない";
                break;
            case ifPlugin::SET_IMMOBILE:
                $message = "プレイヤーを動けないようにする";
                break;
            case ifPlugin::UNSET_IMMOBILE:
                $message = "プレイヤーを動けるようにする";
                break;
            case ifPlugin::EVENT_CANCELL:
                $message = "イベントをキャンセルする";
                break;
        }
        return [
        	"type" => "label",
            "message" => $message,
            "next" => "\n"
        ];
    }
}