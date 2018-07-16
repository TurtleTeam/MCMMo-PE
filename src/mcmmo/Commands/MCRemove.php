<?php
/**
 * Created by PhpStorm.
 * User: Angelo
 * Date: 5/20/2016
 * Time: 4:37 PM
 */

namespace mcmmo\Commands;

use mcmmo\BaseFiles\MCMMOCommand;
use mcmmo\Main;
use pocketmine\command\CommandSender;


class MCRemove extends MCMMOCommand {

    public function __construct(Main $plugin){
        parent::__construct("mcremove", $plugin);
        $this->setUsage("/mcremove <playername>");
        $this->setDescription("Removes a player from the MCMMO database");
        $this->setPermission("mcmmo.admin");
    }

    public function execute(CommandSender $sender, $commandLabel, array $args) {
        if(!$this->testPermission($sender)) {
            return false;
        }

        if(count($args) === 0 || count($args) > 1) {
            $sender->sendMessage($this->getPlugin()->colorize("&7&oUsage: /mcremove <playername>"));
            return false;
        }

        if($player = $this->getPlugin()->getServer()->getPlayer($args[0]) == null) {
            $sender->sendMessage($this->getPlugin()->colorize("&c[Error] &oPlayer dosetn exist in the database!"));
            return false;
        }
    }
}