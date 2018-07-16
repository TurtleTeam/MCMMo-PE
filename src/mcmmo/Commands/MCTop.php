<?php
/**
 * Created by PhpStorm.
 * User: Angelo
 * Date: 5/28/2016
 * Time: 4:27 AM
 */

namespace mcmmo\Commands;


use mcmmo\BaseFiles\MCMMOCommand;
use mcmmo\Main;
use pocketmine\command\CommandSender;

class MCTop extends MCMMOCommand
{

    public function __construct($name, Main $plugin)
    {
        parent::__construct($name, $plugin);
        $this->setUsage("/mctop");
        $this->setDescription("Check who rules the server!");
        $this->setPermission("mcmmo.top");
    }

    public function execute(CommandSender $sender, $commandLabel, array $args)
    {

        $sender->sendMessage($this->getPlugin()->colorize($this->getPlugin()->top));
    }

}