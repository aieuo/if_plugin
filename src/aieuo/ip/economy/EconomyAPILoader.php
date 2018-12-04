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

	public function getMoney(string $name){
		return (int)$this->getPlugin()->mymoney($name);
	}

	public function addMoney(string $name, int $money){
        $this->getPlugin()->addMoney($name, $money);
		return true;
	}

	public function takeMoney(string $name, int $money){
        $this->getPlugin()->reduceMoney($name, $money);
		return true;
	}
}