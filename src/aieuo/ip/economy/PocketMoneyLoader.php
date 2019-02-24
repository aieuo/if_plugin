<?php

namespace aieuo\ip\economy;

class PocketMoneyLoader implements EconomyLoader{

	private $plugin;

	public function __construct($plugin){
		$this->plugin = $plugin;
	}

	public function getPlugin(){
		return $this->plugin;
	}

	public function getMoney(string $name){
		return (int)$this->getPlugin()->getMoney($name);
	}

	public function addMoney(string $name, int $money){
		$mymoney = $this->getMoney($name);
        $this->getPlugin()->setMoney($name, $mymoney + $money);
		return true;
	}

	public function takeMoney(string $name, int $money){
		$mymoney = $this->getMoney($name);
        $this->getPlugin()->setMoney($name, $mymoney - $money);
		return true;
	}
}