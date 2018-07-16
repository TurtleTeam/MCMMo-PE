<?php
/**
 * Created by PhpStorm.
 * User: Angelo
 * Date: 5/23/2016
 * Time: 9:26 PM
 */

namespace mcmmo;

use pocketmine\scheduler\PluginTask;

class Update extends PluginTask {

    private $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
        parent::__construct($plugin);
    }

    public function onRun($currentTick) {
        $this->plugin->top = $this->plugin->getTop();
    }

}