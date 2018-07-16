<?php
/**
 * Created by PhpStorm.
 * User: Angelo
 * Date: 5/23/2016
 * Time: 9:26 PM
 */

namespace mcmmo;

use pocketmine\scheduler\Task;
use mcmmo\Main;

class TapLog extends Task {

    private $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function onRun($currentTick) {
        foreach($this->plugin->ability as $player => $time) {
            if($this->plugin->ability[$player]["time"] <= time()) {
                unset($this->plugin->ability[$player]);
            }
        }
        $this->plugin->tap = [];
    }

}