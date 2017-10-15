<?php

/*
 * Copyright (C) 2017 RTG
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace RTG\ChatFilter;

/* Essentials */

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as TF;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\player\PlayerChatEvent;

class Loader extends PluginBase implements Listener {

    public $cfg;
    const prefix = '[ChatFilter]';

    public function onEnable() {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        if (!is_dir($this->getDataFolder())) {
            mkdir($this->getDataFolder());
            if (!is_file($this->getDataFolder() . "words.txt")) {
                $this->cfg = new Config($this->getDataFolder() . "words.txt", Config::ENUM);
            }
        } else {
            $this->getLogger()->warning(self::prefix . " Folder Loaded!");
        }
    }

    /**
     * @param CommandSender $sender
     * @param Command $command
     * @param string $commandLabel
     * @param array $args
     * @return bool
     */
    public function onCommand(CommandSender $sender, Command $command, string $commandLabel, array $args): bool
    {
        switch (strtolower($command->getName())) {
            case "cf":
                if ($sender->hasPermission("chatfilter.command") or $sender->isOp()) {
                    if (isset($args[0])) {
                        switch (strtolower($args[0])) {
                            case "add":
                                if (isset($args[1])) {
                                    $word = $args[1];
                                    if ($this->cfg->exists($word, true)) {
                                        $sender->sendMessage("[ChatFilter] $word exists!");
                                    } else {
                                        $this->cfg->set(strtolower($word));
                                        $this->cfg->save();
                                        $sender->sendMessage("[ChatFilter] Added!");
                                    }
                                } else {
                                    $sender->sendMessage("[ChatFilter] /cf add [word]");
                                }

                                return true;

                            case "rm":
                                if (isset($args[1])) {
                                    $word = $args[1];
                                    if ($this->cfg->exists($word)) {
                                        $this->cfg->remove(strtolower($word));
                                        $this->cfg->save();
                                        $sender->sendMessage("[ChatFilter] $word has been removed!");
                                    } else {
                                        $sender->sendMessage("[ChatFilter] $word isn't even in the list!");
                                    }
                                } else {
                                    $sender->sendMessage("[ChatFilter] /cf rm [word]");
                                }

                                return true;

                            case "list":
                                $list = $this->cfg->getAll(true);
                                $msg = implode(", ", $list);
                                $sender->sendMessage(" -- Banned Words -- ");
                                $sender->sendMessage($msg);

                                return true;
                        }
                    } else {
                        $sender->sendMessage("[ChatFilter] /cf < add | rm | list >");
                    }
                } else {
                    $sender->sendMessage(TF::RED . "You have no permission to use this command!");
                }

                return true;
        }
    }

    public function onChat(PlayerChatEvent $e) {
        $p = $e->getPlayer();
        $n = $p->getName();
        $msg = $e->getMessage();

        foreach ($this->cfg->getAll(true) as $banned) {
            
            $find = stripos($msg, $banned);

            if($find !== false) {
                $p->sendMessage(TF::RED . "[ChatFilter] Word blocked!");
                $e->setCancelled();
                
            }
        }
    }
}
