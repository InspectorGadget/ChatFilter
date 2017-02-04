<?php

namespace RTG\ChatFilter\CMD;

/* Essentials */
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use RTG\ChatFilter\Loader;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as TF;

class FilterCommand implements CommandExecutor {
	
	public $plugin;
	
	public function __construct(Loader $plugin) {
		$this->plugin = $plugin;
	}
	
	public function onCommand(CommandSender $sender, Command $cmd, $label, array $param) {
		switch(strtolower($cmd->getName())) {
			
                            case "cf":
			
				if($sender->hasPermission("chatfilter.command")) {
					
					if(isset($param[0])) {
						
						switch(strtolower($param[0])) {
							
							case "help":
							
								$sender->sendMessage("[ChatFilter] /cf add [name]");
								$sender->sendMessage("[ChatFilter] /cf rm [name]");
                                                                $sender->sendMessage("[ChatFilter] /cf help");
							
								return true;
							break;
							
							case "add":
								
								if(isset($param[1])) {
									
									$n = $param[1];
									
										if(isset($this->plugin->whitelist[strtolower($n)])) {
											
											$sender->sendMessage("[ChatFilter] The word '$n' Exists!");
											
										}
										else {
											
											$this->plugin->whitelist[strtolower($n)] = $n;
											$sender->sendMessage("[ChatFilter] You have successfully added $n to the ban list!");
											$this->plugin->onSave();
											
										}
									
								}
								else {
									$sender->sendMessage("[ChatFilter] /cf help");
								}
							
								return true;
							break;
                                                        
                                                        case "rm":
                                                            
                                                            if(isset($param[1])) {
                                                                
                                                                $n = $param[1];
                                                                
                                                                    if(isset($this->plugin->whitelist[strtolower($n)])) {
                                                                        unset($this->plugin->whitelist[strtolower($n)]);
                                                                        $sender->sendMessage("[ChatFilter] You have removed $n from the list!");
                                                                        $this->plugin->onSave();
                                                                    }
                                                                    else {
                                                                        $sender->sendMessage("[ChatFilter] The word $n doesn't exist in the List!");  
                                                                    }
                                                                  
                                                            }
                                                            else {
                                                                $sender->sendMessage("[ChatFilter] /cf help");
                                                            }
                                                            
                                                            return true;
                                                        break;
                                                        
                                                        case "list":
                                                            
                                                            $list = new Config($this->plugin->getDataFolder() . "bannednames.txt", Config::ENUM);
                                                            $l = $list->getAll(true);
                                                            $im = implode(", ", $l);
                                                            $sender->sendMessage($im);
                                                            
                                                            return true;
                                                        break;
								
						}
							
					}
					else {
						$sender->sendMessage("[ChatFilter] /cf help");
					}

				}
				else {
					$sender->sendMessage(TF::RED . "You have no permission to use this command!");
				}
			
				return true;
                            break;
			
		}
		
	}
		
}
