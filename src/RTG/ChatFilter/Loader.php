<?php

namespace RTG\ChatFilter;

/* Essentials */
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;

/* Execution */
use pocketmine\command\CommandExecutor;

use RTG\ChatFilter\CMD\FilterCommand;

class Loader extends PluginBase implements Listener {
	
	public $whitelist;
	
	public function onEnable() {
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		
		/* For Commands */
		$this->getCommand("cf")->setExecutor(new FilterCommand ($this));
		
		/* CFG Setup */
		
		$this->whitelist = array();
		
		@mkdir ($this->getDataFolder());
		$list = new Config($this->getDataFolder() . "bannednames.txt", Config::ENUM);
		$this->whitelist = $list->getAll(true);
		
		$this->getLogger()->info("[LEET] ChatFilter has been enabled!");
	}
	
	public function onSave() {
		$list = new Config($this->getDataFolder() . "bannednames.txt", Config::ENUM);
		$list->setAll($this->whitelist);
		$list->save();
	}
	
	public function onDisable() {
		$this->onSave();
	}
	
}
