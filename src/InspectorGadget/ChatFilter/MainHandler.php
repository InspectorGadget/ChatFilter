<?php

/**
*	
* .___   ________ 
* |   | /  _____/ 
* |   |/   \  ___ 
* |   |\    \_\  \
* |___| \______  /
*              \/ 
*
* All rights reserved InspectorGadget (c) 2018
*
*
**/

namespace InspectorGadget\ChatFilter;

use pocketmine\plugin\PluginBase;
use pocketmine\event\{ Listener, player\PlayerChatEvent };
use pocketmine\utils\{ Config, TextFormat as TF };
use pocketmine\command\{ CommandSender, Command };

class MainHandler extends PluginBase implements Listener {

    public $returnList;

    public function onEnable(): void {

        if (!is_dir($this->getDataFolder())) {
            @mkdir($this->getDataFolder());
        }

        if (!is_file($this->getDataFolder() . "config.yml")) {
            $this->saveDefaultConfig();
        }
        
        $this->returnList = $this->getConfig()->get("banned-words", []);

        if ($this->getConfig()->get("enable") !== true) {
            $this->getLogger()->info("I've been disabled!");
            $this->getServer()->getPluginManager()->disablePlugin($this);
        }

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->info("I'm ready!");

    }

    public function addToList($word, CommandSender $sender) {
        if (in_array($word, $this->returnList)) {
            $sender->sendMessage(TF::RED . "Word $word exists!");
            return true;
        }

        array_push($this->returnList, $word);
        $this->getConfig()->set("banned-words", $this->returnList);
        $this->getConfig()->save();
        $sender->sendMessage(TF::GREEN . "Word $word has been added!");
    }

    public function removeFromList($word, CommandSender $sender) {
        if (!in_array($word, $this->returnList)) {
            $sender->sendMessage(TF::RED . "Word $word is not in the list!");
            return true;
        }

        unset($this->returnList[array_search($word, $this->returnList)]);
        $this->getConfig()->set("banned-words", $this->returnList);
        $this->getConfig()->save();
        $sender->sendMessage(TF::GREEN . "Word $word has been removed!");
    }

    public function returnList(CommandSender $sender) {
        $sender->sendMessage(TF::GREEN . " -- Banned Words --");
        foreach ($this->returnList as $word) {
            $sender->sendMessage("- " . $word);
        }
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        switch (strtolower($command->getName())) {
            case "cf":

                if (!$sender->hasPermission("chatfilter.command") || !$sender->isOp()) {
                    $sender->sendMessage(TF::RED . "You have no permission to use this command!");
                    return true;
                }

                if (!isset($args[0])) {
                    $sender->sendMessage(TF::GREEN . "[Usage] /cf help");
                    return true;
                }

                switch (strtolower($args[0])) {
                    case "help":
                        $sender->sendMessage(TF::GREEN . "- /cf add {word} || Adds a word to the list! \n - /cf remove {word} || Removes a word from your list!");
                    break;
                    case "add":
                        if (!isset($args[1])) {
                            $sender->sendMessage(TF::GREEN . '[Usage] /cf add {word}');
                            return true;
                        }

                        $word = strtolower($args[1]);
                        $this->addToList($word, $sender);
                    break;
                    case "remove":
                        if (!isset($args[1])) {
                            $sender->sendMessage(TF::GREEN . '[Usage] /cf remove {word}');
                            return true;
                        }
                        
                        $word = $args[1];
                        $this->removeFromList($word, $sender);
                    break;
                    case "list":
                        $this->returnList($sender);
                    break;
                }

                return true;
            break;
        }
    }

    public function onChat(PlayerChatEvent $event) {
        $message = $event->getMessage();
        $username = $event->getPlayer()->getName();
        $caps = (int) $this->getConfig()->get("caps-limit");

        # Caps Limit Code Block from CapsLimit (Poggit Orphanage!)
        $strlen = strlen($message);
        $asciiA = ord("A");
        $asciiZ = ord("Z");
        $count = 0;
        for($i = 0; $i < $strlen; $i++){
          $char = $message[$i];
          $ascii = ord($char);
          if($asciiA <= $ascii and $ascii <= $asciiZ){
            $count++;
          }
        }
        # --- Thank you Poggit :) ---

        if (!$event->getPlayer()->hasPermission("chatfilter.bypass")) {

            if ($count > $caps) {
                $event->getPlayer()->sendMessage(TF::RED . "[ChatFilter] Caps Limit is set to $caps!");
                $event->setCancelled();
                return true;
            }

            foreach ($this->returnList as $word) {
                $digger = stripos(strtolower($message), strtolower($word));
                if ($digger !== false) {
                    if ($this->getConfig()->get("log-restricted-words") !== false) {
                        $this->getLogger()->info("Swear word used : $word : by player $username!");
                    }

                    $replace = $this->getConfig()->get("replace-word");
                    if ($replace !== false) {
                        $filtered_message = str_replace($word, "****", $message);
                        $event->setMessage($filtered_message);
                        return true;
                    }

                    $event->getPlayer()->sendMessage(TF::RED  . "[ChatFilter] Word $word is blocked!");
                    $event->setCancelled();
                    return true;
                }
            }

        }
    }

    public function onDisable(): void {
        $this->getLogger()->info("I'm done! Bye");
    }

}