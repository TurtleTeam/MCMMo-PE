<?php
/**
 * Created by PhpStorm.
 * User: Angelo
 * Date: 5/20/2016
 * Time: 7:31 PM
 */

namespace mcmmo;

use pocketmine\command\CommandSender;
use pocketmine\level\sound\EndermanTeleportSound;
use pocketmine\Player;

class MCPlayer {

    private $plugin;
    public $player;
    public $name;
    public $parse;
    public $stats = [];
    public $cooldowns = [];

    const ACROBATICS = "acro";
    const ARCHERY = "arch";
    const UNARMED = "unarmed";
    const SWORDS = "swords";
    const AXES = "axes";
    const MINING = "mining";
    const WOOD = "wood"; 
    const HERB = "herb"; 
    const DIG = "dig";

    public function __construct(Main $plugin, Player $player, $stats) {
        $this->plugin = $plugin;
        $this->player = $player;
        $this->name = $player->getName();
        $this->parse = $stats;
        $this->parseStats();
    }
    
    public function parseStats() {
        $skills = explode("|", $this->parse);
        foreach($skills as $skill) {
            $arr = explode(":", $skill);
            $type = $arr[0];
            $exp= $arr[1];
            $level = $arr[2];
            $name = $arr[3];
            $this->stats[$type]["exp"] = $exp;
            $this->stats[$type]["level"] = $level;
            $this->stats[$type]["name"] = $name;
        }
    }

    public function getStats() {
        $strstats = "";
        foreach($this->stats as $stat => $untouched) {
            $exp = $this->stats[$stat]["exp"];
            $level = $this->stats[$stat]["level"];
            $name = $this->stats[$stat]["name"];
            $strstats .= "$stat:$exp:$level:$name|";
        }
        return trim($strstats, "|");
    }

    public function getTop() {
        $hold = 0;
        foreach($this->stats as $stat => $untouched) {
            $exp = $this->stats[$stat]["level"];
            $hold = $hold + $exp;
        }
        return $hold;
    }

    public function sendStats(CommandSender $rec) {
        $msg = "";
        $levels = [];
        foreach($this->stats as $stat => $untouched) {
            $exp = $this->stats[$stat]["exp"];
            $level = $this->stats[$stat]["level"];
            if($exp == 0) {
                $levels[$stat]["level"] = 0;
            } else {
                $levels[$stat]["level"] = $level;
            }
            $levels[$stat]["name"] = $this->stats[$stat]["name"];
        }
        $msg .= "&7 >==< §6McMMO§7 >==<\n&r&a&o    " . $levels["acro"]["name"] . ": &r&e". $levels["acro"]["level"] . "&r\n&r&a&o    " . $levels["arch"]["name"] . ": &r&e". $levels["arch"]["level"] ."&r\n&r&a&o    " . $levels["unarmed"]["name"] . ": &r&e". $levels["unarmed"]["level"] ."&r\n&r&a&o    " . $levels["swords"]["name"] . ": &r&e". $levels["swords"]["level"]  . "&r\n&r&a&o    " . $levels["axes"]["name"] . ": &r&e". $levels["axes"]["level"]  . "&r\n&r&a&o    " . $levels["mining"]["name"] . ": &r&e". $levels["mining"]["level"]  . "\n&r&a&o    " . $levels["herb"]["name"] . ": &r&e". $levels["herb"]["level"]  . "&r\n&r&a&o    " . $levels["wood"]["name"] . ": &r&e". $levels["wood"]["level"]  . "&r\n&r&a&o    " . $levels["dig"]["name"] . ": &r&e". $levels["dig"]["level"]  . "&r\n&r&7 =-=-=-=-=-=-=-=";
        $rec->sendMessage($this->plugin->colorize($msg));
    }

    public function updateLevel($type, $level) {
       $this->stats[$type]["level"] = $level;
    }

    public function saveStats() {
        $stats = $this->getStats();
        $top = $this->getTop();
        $name = $this->name;
        $stmt = $this->plugin->db->query("UPDATE master SET stats='$stats', top='$top' WHERE player='$name';");
    }

    public function calculateExp($exp) {
        $bef = null;
        $aft = null;
        $t = 1;
        for($i = 1; $i <= 999999; $i++) {
            $eq = (10 * ($i*$i)) + (1010 * $i);
            if($eq == $exp) {
                return $i;
            }
            $bef = $i - 1;
            $aft = $i + 1;
            echo "";
            if(((10 * ($t*$t)) + (1010 * $t)) / 1010 > ($exp / 1010)) {
                break;
            }
            $t++;
        }
        $eq1 = (10 * ($bef*$bef)) + (1010 * $bef);
        $eq2 = (10 * ($aft*$aft)) + (1010 * $aft);
        if($exp > $eq1 && $exp < $eq2) {
            return $bef;
        }
        if($exp > $eq2) {
            return $aft;
        }
    }

    public function levelUp($stat) {
        $sound = new EndermanTeleportSound($this->player);
        $this->player->getLevel()->addSound($sound);
        $name = $this->stats[$stat]["name"];
        $level = $this->stats[$stat]["level"];
        $this->player->sendMessage($this->plugin->colorize("&7------------------\n&a&l     - $name -     \n&e      &rLevel $level\n&7------------------"));
    }

    public function addExp($skill, $exp) {
        $this->stats[$skill]["exp"] = $this->stats[$skill]["exp"] + $exp;
        $currlevel = $this->stats[$skill]["level"];
        $newlevel = $this->calculateExp($this->stats[$skill]["exp"]);
        if($newlevel > $currlevel) {
            $this->updateLevel($skill, $newlevel);
            $this->levelUp($skill);
        }
    }

}