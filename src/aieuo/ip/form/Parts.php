<?php

namespace aieuo\ip\form;

class Parts {

	public static function getIfList(){
    	$list = [
            "何も確認しない",
            "お金を減らす",
            "指定した金額より所持金が多いか",
            "指定したアイテムを手に持ってるか",
            "インベントリに指定したアイテムが入ってるか",
            "指定したアイテムがインベントリにあるなら削除する",
            "プレイヤーがopか",
            "プレイヤーがスニークしているか",
            "プレイヤーが飛んでいるか",
            "ゲームモードが指定したものだったら",
            "指定した範囲内にいたら",
            "乱数が指定したものだったら",
            "二つの値を比較する",
        ];
        return $list;
	}
    public static function getIflistDropdown($default = 0){
    	$options = self::getIfList();
        $dropdown = Elements::getDropdown("もし～なら", $options, $default);
    	return $dropdown;
    }

    public static function getExeList(){
    	$list = [
            "何もしない",
            "チャット欄にメッセージ送信",
            "tip欄にメッセージ送信",
            "タイトル送信",
            "全体にメッセージ送信",
            "opだけにメッセージ送信",
            "音声付きのメッセージを送信",
            "コマンドを実行する",
            "コンソールからコマンド実行",
            "遅れてコマンド実行",
            "テレポート",
            "動かす",
            "インベントリにアイテムを追加する",
            "インベントリからアイテムを削除する",
            "プレイヤーを動けなくする",
            "プレイヤーを動けるようにする",
            "手に持ってるアイテムにエンチャントを追加する",
            "エフェクトを与える",
            "表示する名前を変更する",
            "寝かせる",
            "座らせる",
            "ゲームモードを変更する",
            "体力を変更する",
            "最大体力を変更する",
            "攻撃する",
            "キックする",
        ];
        return $list;
    }
    public static function getExelistDropdown($default = 0){
    	$options = self::getExeList();
        $dropdown = Elements::getDropdown("条件に当てはまったら～をする", $options, $default);
    	return $dropdown;
    }

    public static function getEventListDropdown(){
    	$options = [
            "プレイヤーがチャットしたとき",
            "プレイヤーがコマンドを実行したとき",
            "プレイヤーがブロックを触ったとき",
            "プレイヤーがサーバーに参加したとき",
            "プレイヤーがサーバーから退室したとき",
            "プレイヤーがブロックを壊したとき",
            "プレイヤーがブロックを置いたとき",
            "プレイヤーがダメージを受けたとき",
            "プレイヤーがフライ状態を切り替えたとき",
            "プレイヤーが死亡したとき",
        ];
        $dropdown = Elements::getDropdown("イベントを選んでください", $options);
    	return $dropdown;
    }
}