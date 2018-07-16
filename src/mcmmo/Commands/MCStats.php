<?php
/**
 * Created by PhpStorm.
 * User: Angelo
 * Date: 5/20/2016
 * Time: 4:37 PM
 */

namespace mcmmo\Commands;

use mcmmo\BaseFiles\MCMMOCommand;
use mcmmo\MCPlayer;
use mcmmo\Main;
use pocketmine\Player;
use pocketmine\command\CommandSender;


class MCStats extends MCMMOCommand {

    public function __construct($name, Main $plugin){
        parent::__construct("mcstats", $plugin);
        $this->setUsage("/mcstats [playername]");
        $this->setDescription("Check a your own or a players stats.");
        $this->setPermission("mcmmo.stats");
    }

    public function execute(CommandSender $sender, $commandLabel, array $args) {

        if(count($args) > 1) {
            $sender->sendMessage($this->getPlugin()->colorize("&7&oUsage: /mcstats [playername]"));
            return false;
        }

        if(!$sender instanceof Player && !isset($args[0])) {
            $sender->sendMessage($this->getPlugin()->colorize("&c[Error] &oSilly console, you dont have any stats!"));
            return false;
        }

        $player = $sender->getName();

        if(isset($args[0]) && !$this->getPlugin()->ifPlayerExists($args[0])) {
            $sender->sendMessage($this->getPlugin()->colorize("&c[Error] &oPlayer does not exist in the database!"));
            return false;
        }

        if(isset($args[0]) && $this->getPlugin()->ifPlayerExists($args[0])) {
            $player = $args[0];
        }
        
        if($this->getPlugin()->getServer()->getPlayer($player) == null) {
            $sender->sendMessage($this->getPlugin()->getOfflineStats($player));
            return true;
        }

        if(!$sender instanceof Player) {
            if($this->getPlugin()->getServer()->getPlayer($player) != null) {
                $data = $this->getPlugin()->players[strtolower($player)];
                if($data instanceof MCPlayer) {
                    $data->sendStats($sender);
                    return true;
                }
            }
            $sender->sendMessage($this->getPlugin()->getOfflineStats($player));
            return true;
        }

        $data = $this->getPlugin()->players[strtolower($player)];
        if($data instanceof MCPlayer) {
            $data->sendStats($sender);
            return true;
        }
    }
}