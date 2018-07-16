<?php

namespace mcmmo;

use mcmmo\Commands\MCStats;
use mcmmo\Commands\MCTop;
use mcmmo\MCPlayer;
use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use mcmmo\EventListener;

class Main extends PluginBase {

    public $db;
    public $tap = [];
    public $top;
    public $ability = [];
    public $players = [];

    public function onEnable()
    {
        @mkdir($this->getDataFolder());
        $this->saveDefaultConfig();
        $this->reloadConfig();
        $this->getConfig()->save();
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getServer()->getCommandMap()->register("mcstats", new MCStats("mcstats", $this));
        $this->getServer()->getCommandMap()->register("mctop", new MCTop("mctop", $this));

        /* Database Configuration */

        $this->db = new \SQLite3($this->getDataFolder() . "MCMMO.db");
        $this->db->exec("CREATE TABLE IF NOT EXISTS master (player TEXT PRIMARY KEY COLLATE NOCASE, stats TEXT VARCHAR, timestamp INT);");
        $result = $this->db->query("PRAGMA table_info(master)");
        $tell = null;
        while($row = $result->fetchArray(SQLITE3_ASSOC)) {
            if($row["name"] == "top") {
                $tell = $row["name"];
            }
        }
        if($tell == "top") {
            $this->getLogger()->info($this->colorize("&bYour are updated! Keep up to date with LilCrispy2o9 ;)"));
        } else {
            $this->db->exec("ALTER TABLE master ADD COLUMN top INT");
        }

        /*
        $stmt = $this->db->prepare("INSERT OR REPLACE INTO master (player, stats, timestamp) VALUES (:player, :stats, :timestamp);");
        $stmt->bindValue(":player", "testuser");
        $stmt->bindValue(":stats", "unarmed:0|swords:0|axes:0|mining:0|wood:0|herb:0|dig:0|");
        $stmt->bindValue(":timestamp", time());
        $result = $stmt->execute();
        */

        $this->getScheduler()->scheduleRepeatingTask(new TapLog($this), 20);
        $this->top = $this->getTop();
        $this->getScheduler()->scheduleRepeatingTask(new TapLog($this), 20 * 60 * 5);
    }

    public function onDisable() {
        $this->saveAll();
    }
    public function checkTime($data, $stat) {
        if($data instanceof MCPlayer) {
            if($data->cooldowns[$stat] <= time()) {
                unset($data->cooldowns[$stat]);
                return;
            }
        }
    }

    public function getTop() {
        $msg = "&7  >==< §dMcMMO Top§7 >==<\n&r";
        $i = 1;
        $result = $this->db->query("SELECT player, top FROM master ORDER BY top DESC LIMIT 5");
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $name1 = $row["player"];
            $top = $row["top"];
            $rank = $i;
            $msg .= "&a$rank. &e&o$name1 - $top\n";
            $i++;
        }
        return $msg;
    }

    public function saveAll() {
        foreach($this->players as $p) {
            if($p instanceof MCPlayer) {
                $stats = $p->getStats();
                $top = $p->getTop();
                $name = $p->name;
                $stmt = $this->db->query("UPDATE master SET stats='$stats', top='$top' WHERE player='$name';");
            }
        }
        $this->getLogger()->info($this->colorize("&bAll stats saved and cache cleared!"));
    }

    public function addPlayer($name) {
        $name = strtolower($name);
        $stmt = $this->db->prepare("INSERT OR REPLACE INTO master (player, stats, timestamp) VALUES (:player, :stats, :timestamp);");
        $stmt->bindValue(":player", $name);
        $stmt->bindValue(":stats", "acro:0:0:Acrobatics|arch:0:0:Archery|unarmed:0:0:Unarmed|swords:0:0:Swords|axes:0:0:Axes|mining:0:0:Mining|wood:0:0:Wood-Cutting|herb:0:0:Herbalism|dig:0:0:Excavation");
        $stmt->bindValue(":timestamp", time());
        $result = $stmt->execute();
    }

    public function removePlayer($name) {
        $name = strtolower($name);
        $stmt = $this->db->query("DELETE FROM master WHERE player='$name';");
    }

    public function updatePlayerTime($name) {
        $name = strtolower($name);
        $time = time();
        $stmt = $this->db->query("UPDATE master SET timestamp='$time' WHERE player='$name';");
    }

    public function ifPlayerExists($name) {
        $name = strtolower($name);
        $result = $this->db->query("SELECT * FROM master WHERE player='$name';");
        $array = $result->fetchArray(SQLITE3_ASSOC);
        return empty($array) == false;
    }

    public function getStats($name) {
        $name = strtolower($name);
        $result = $this->db->query("SELECT * FROM master WHERE player='$name';");
        $array = $result->fetchArray(SQLITE3_ASSOC);
        if(empty($array) == true) {
            return false;
        }
        return $array["stats"];
    }

    public function getOfflineStats($name) {
        $name = strtolower($name);
        $result = $this->db->query("SELECT * FROM master WHERE player='$name';");
        $array = $result->fetchArray(SQLITE3_ASSOC);
        if(empty($array) == true) {
            return false;
        }
        $parse = $array["stats"];
        $skills = explode("|", $parse);
        $stats = [];
        foreach($skills as $skill) {
            $arr = explode(":", $skill);
            $type = $arr[0];
            $exp = $arr[1];
            $level = $arr[2];
            $name = $arr[3];
            $stats[$type]["exp"] = $exp;
            $stats[$type]["level"] = $level;
            $stats[$type]["name"] = $name;
        }
        $msg = "";
        $levels = [];
        foreach($stats as $stat => $untouched) {
            $exp = $stats[$stat]["exp"];
            $level = $this->calculateExp($exp);
            if($exp == 0) {
                $levels[$stat]["level"] = 0;
            } else {
                $levels[$stat]["level"] = $level;
            }
            $levels[$stat]["name"] = $stats[$stat]["name"];
        }
        $msg .= "&7  >==< &6McMMO&7 >==<\n&r&a&o" . $levels["acro"]["name"] . ": &r&e". $levels["acro"]["level"] ."&r\n&r&a&o" . $levels["arch"]["name"] . ": &r&e". $levels["arch"]["level"] ."&r\n&r&a&o" . $levels["unarmed"]["name"] . ": &r&e". $levels["unarmed"]["level"] ."&r\n&r&a&o" . $levels["swords"]["name"] . ": &r&e". $levels["swords"]["level"]  . "&r\n&r&a&o" . $levels["axes"]["name"] . ": &r&e". $levels["axes"]["level"]  . "&r\n&r&a&o" . $levels["mining"]["name"] . ": &r&e". $levels["mining"]["level"]  . "\n&r&a&o" . $levels["herb"]["name"] . ": &r&e". $levels["herb"]["level"]  . "&r\n&r&a&o" . $levels["wood"]["name"] . ": &r&e". $levels["wood"]["level"]  . "&r\n&r&a&o" . $levels["dig"]["name"] . ": &r&e". $levels["dig"]["level"]  . "&r\n&r&7-=-=-=-=-=-=-";
        return $this->colorize($msg);
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

    public function colorize($str) {
        return str_replace("&", "§", $str);
    }

}