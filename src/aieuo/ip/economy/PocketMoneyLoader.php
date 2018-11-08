<?php

namespace aieuo\ip\economy;

class PocketMoneyLoader extends EconomyLoader{

	private $plugin;

	public function __construct($plugin){
		$this->plugin = $plugin;
	}

	public function getPlugin(){
		return $this->plugin;
	}

	public function getMoney($name){
		return (int)$this->plugin->getMoney($name);
	}

	public function addMoney($name, $money){
		$money = (int)$money;
		$mymoney = $this->plugin->getMoney($name);
        $this->plugin->setMoney($name, $mymoney + $money);
		return true;
	}

	public function takeMoney($name, $money){
		$money = (int)$money;
		$mymoney = $this->plugin->getMoney($name);
        $this->plugin->setMoney($name, $mymoney - $money);
		return true;
	}
}