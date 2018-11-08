<?php

namespace aieuo\ip\economy;

class EconomyAPILoader extends EconomyLoader{

	private $plugin;

	public function __construct($plugin){
		$this->plugin = $plugin;
	}

	public function getPlugin(){
		return $this->plugin;
	}

	public function getMoney($name){
		return (int)$this->plugin->mymoney($name);
	}

	public function addMoney($name, $money){
		$money = (int)$money;
        $this->plugin->addMoney($name, $money);
		return true;
	}

	public function takeMoney($name, $money){
		$money = (int)$money;
        $this->plugin->reduceMoney($name, $money);
		return true;
	}
}