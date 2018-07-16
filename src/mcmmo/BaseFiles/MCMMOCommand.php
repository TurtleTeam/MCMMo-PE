<?php

namespace mcmmo\BaseFiles;

use mcmmo\Main;
use pocketmine\command\Command;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\plugin\Plugin;

abstract class MCMMOCommand extends Command implements PluginIdentifiableCommand {

    private $plugin;

    public function __construct($name, Main $plugin){
        parent::__construct($name);
        $this->plugin = $plugin;
        $this->usageMessage = "";
    }

    public function getPlugin() : Plugin {
        return $this->plugin;
    }

}