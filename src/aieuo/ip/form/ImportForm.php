<?php

namespace aieuo\ip\form;

use aieuo\ip\ifPlugin;
use aieuo\ip\Session;
use aieuo\ip\utils\Messages;

class ImportForm {
	public function getImportListForm($mes = "") {
		$buttons = [Elements::getButton("<ひとつ前のページに戻る>")];
		$files = glob(ifPlugin::getInstance()->getDataFolder()."imports/*.json");
		foreach($files as $file){
			if(is_dir($file)) continue;
			$datas = json_decode(file_get_contents($file), true);
			$buttons[] = Elements::getButton($datas["name"]." | ".$datas["author"]);
		}
		$data = [
			"type" => "form",
			"title" => "ファイル選択",
			"content" => ($mes === "" ? "" : $mes."\n")."§7ボタンを押してください",
			"buttons" => $buttons
		];
		$json = Form::encodeJson($data);
		return $json;
	}

	public function onImportList($player, $data) {
		$session = $player->ifSession;
		if($data === null) {
			$session->setValid(false, false);
			return;
		}
		if($data == 0) {
			$data = (new Form())->getSelectIfTypeForm();
			Form::sendForm($player, $data, new Form(), "onSelectIfType");
			return;
		}
		$files = glob(ifPlugin::getInstance()->getDataFolder()."imports/*.json");
		if(!isset($files[$data - 1])) {
			$form = $this->getImportListForm("エラーが発生しました、もう一度選択してください");
			Form::sendForm($player, $form, $this, "onImportList");
			return;
		}
		$path = $files[$data - 1];
		$session->setData("path", $path);
		$form = $this->getImportForm(json_decode(file_get_contents($path), true));
		Form::sendForm($player, $form, $this, "onImport");
	}

	public function getImportForm($datas) {
		$mes = "";
		foreach ($datas["ifs"] as $key => $value) {
			$mes .= "§l".$key."§r§f\n".Messages::createMessage($value["if"], $value["match"], $value["else"])."\n";
			$mes .= "---------------------------";
		}
		$data = [
			"type" => "custom_form",
			"title" => "ファイルインポート",
			"content" => [
				Elements::getLabel($mes),
				Elements::getToggle("キャンセル")
			]
		];
		$json = Form::encodeJson($data);
		return $json;
	}

	public function onImport($player, $data) {
		$session = $player->ifSession;
		if($data === null) {
			$session->setValid(false, false);
			return;
		}
		if($data[1]) {
			$form = $this->getImportListForm("キャンセルしました");
			Form::sendForm($player, $form, $this, "onImportList");
			return;
		}
		$file = json_decode(file_get_contents($session->getData("path")), true);
		$this->importDatas($player, $file);
	}

	public function importDatas($player, $file, $count = 0) {
		$session = $player->ifSession;
		foreach ($file["ifs"] as $key => $datas) {
			if($datas["type"] === Session::BLOCK) {
				$manager = ifPlugin::getInstance()->getBlockManager();

				if($manager->isAdded($key) and !isset($session->getData("overwrite")[$key])) {
					$session->setData("file", $file);
					$session->setData("if_key", $key);
					$session->setData("count", $count);
					$form = $this->getConfirmOverwriteForm($key);
					Form::sendForm($player, $form, $this, "onConfirmOverwrite");
					return;
				} elseif($manager->isAdded($key) and !$session->getData("overwrite")[$key]) {
					continue;
				}

				$manager->set($key, [
					"if" => $datas["if"],
					"match" => $datas["match"],
					"else" => $datas["else"],
					"author" => $file["author"]
				]);
				$count ++;

			} elseif($datas["type"] === Session::COMMAND) {
				$manager = ifPlugin::getInstance()->getCommandManager();

				if($manager->exists($key)) continue;
				if($manager->isAdded($key) and !isset($session->getData("overwrite")[$key])){
					$session->setData("file", $file);
					$session->setData("if_key", $key);
					$session->setData("count", $count);
					$form = $this->getConfirmOverwriteForm($key);
					Form::sendForm($player, $form, $this, "onConfirmOverwrite");
					return;
				} elseif($manager->isAdded($key) and !$session->getData("overwrite")[$key]) {
					continue;
				}

				$manager->set($key, [
					"if" => $datas["if"],
					"match" => $datas["match"],
					"else" => $datas["else"],
					"author" => $file["author"]
				], [
					"desc" => $datas["description"],
					"perm" => $datas["permission"]
				]);
				$manager->register($key, $datas["description"], $datas["permission"]);
				$count ++;

			} elseif($datas["type"] === Session::EVENT) {
				$manager = ifPlugin::getInstance()->getEventManager();
				$manager->addByEvent($datas["options"]["eventname"], $datas + ["author" => $file["author"]]);
				$count ++;
			}
			unset($file["ifs"][$key]);
		}
		$player->sendMessage($count."個のIFを追加しました");
		$session->setValid(false);
	}

	public function getConfirmOverwriteForm($key) {
		$data = [
			"type" => "modal",
			"title" => "上書き",
			"content" => $key."は既に存在します、上書きしますか?\n上書きすると以前の物は復元できません。",
			"button1" => "はい",
			"button2" => "いいえ"
		];
		$data = Form::encodeJson($data);
		return $data;
	}

	public function onConfirmOverwrite($player, $data) {
		$session = $player->ifSession;
		if($data === null) {
			$session->setValid(false, false);
			return;
		}
		if($data) {
			if(($overwrite = $session->getData("overwrite")) === "") {
				$session->setData("overwrite", [$session->getData("if_key") => true]);
			} else {
				$overwrite[$session->getData("if_key")] = true;
				$session->setData("overwrite", $overwrite);
			}
			$this->importDatas($player, $session->getData("file"), $session->getData("count"));

		} else {
			if(($overwrite = $session->getData("overwrite")) === "") {
				$session->setData("overwrite", [$session->getData("if_key") => false]);
			} else {
				$overwrite[$session->getData("if_key")] = false;
				$session->setData("overwrite", $overwrite);
			}
			$this->importDatas($player, $session->getData("file"), $session->getData("count"));
		}
	}
}