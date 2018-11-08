<?php

namespace aieuo\ip\economy;

class MoneySystemLoader extends EconomyLoader{

	private $plugin;

	public function __construct($plugin){
		$this->plugin = $plugin;
	}

	public function getPlugin(){
		return $this->plugin;
	}

	public function getMoney($name){
		return (int)$this->plugin->check($name);
	}

	public function addMoney($name, $money){
		$money = (int)$money;
		$this->plugin->addMoney($name, $money);
		return true;
	}

	public function takeMoney($name, $money){
		$money = (int)$money;
		$this->plugin->takeMoney($name, $money);
		return true;
	}
}