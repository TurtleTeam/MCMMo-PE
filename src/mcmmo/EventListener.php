<?php
/**
 * Created by PhpStorm.
 * User: Angelo
 * Date: 5/20/2016
 * Time: 4:59 PM
 */

namespace mcmmo;

use pocketmine\block\Crops;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Monster;
use pocketmine\entity\Arrow;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\level\particle\CriticalParticle;
use pocketmine\Player;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerItemDropEvent;
use pocketmine\utils\Color;


class EventListener implements Listener {

    private $plugin;
    
    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    /*
     * @param EntityDamageEvent $e
     *
     * @priority LOWEST;
     */
    public function onDamage(EntityDamageEvent $e) {
        //ACROBATICS
        if($e->getCause() == EntityDamageEvent::CAUSE_FALL) {
          if(!$e->isCancelled()) {
            if($e->getEntity() instanceof Player) {
              $p = $e->getEntity();
              if($e->getFinalDamage() < $p->getHealth()) {
                 $data = $this->plugin->players[strtolower($p->getName())];
                 $data->addExp(MCPlayer::ACROBATICS, $e->getFinalDamage() * 20);
                 $chances = $data->stats[MCPlayer::ACROBATICS]["level"] / 20 + 1;
                 if(mt_rand(0, 80) < $chances) {
                   $e->setModifier($e->getFinalDamage() * .75, 4);
                   $p->sendPopup("§aRolled Successfully!");
            }
          }
        }
      }
    }

        if($e instanceof EntityDamageByEntityEvent) {
        if(!$e->isCancelled()) {
            $player = $e->getDamager();
            $type = $e->getEntity();
            if($player instanceof Player) {
            $item = $player->getInventory()->getItemInHand()->getId();
                if(isset($this->plugin->ability[strtolower($player->getName())])) {
                    $ability = $this->plugin->ability[strtolower($player->getName())]["ability"];
                    //ARCHERY
                    if($e instanceof EntityDamageByChildEntityEvent) {
                        if($e->getChild() instanceof Arrow) {
                            if($ability == MCPlayer::ARCHERY && $item == 261) {
                                $data = $this->plugin->players[strtolower($player->getName())];
                                $bonus = 1+($data->stats[MCPlayer::ARCHERY]["level"] * 1);

                            if($bonus > 2.5) {
                                $bonus = 2.5;
                            }
                            $e->setModifier($e->getFinalDamage() * $bonus, 7);
                            $player->sendMessage("§aSuccessful hit!");
                            }
                        }
                    }
                }
                    //BERSERK
                    if($ability == MCPlayer::UNARMED && $item == 0) {
                        $e->setModifier($e->getFinalDamage() + 2, 2);
                    }
                    //SWORDS
                    if($ability == MCPlayer::SWORDS && $item == 267 || $ability == MCPlayer::SWORDS && $item == 268 || $ability == MCPlayer::SWORDS && $item == 272 || $ability == MCPlayer::SWORDS && $item == 276 || $ability == MCPlayer::SWORDS && $item == 283) {
                        $damage = $e->getFinalDamage();
                        $newdamage = $damage + $damage / 4;
                        $data = $this->plugin->players[strtolower($player->getName())];
                        $time = $data->stats[MCPlayer::SWORDS]["level"] / 20 + 4;
                        $eff = new EffectInstance(Effect::getEffect(Effect::POISON), $time * 15, 0);
                        $eff->setColor(new Color(150, 0, 0));
                    if(mt_rand(0, 5) <= 1) {
                        $type->addEffect($eff);
                        if ($type instanceOf Player) {
                            $damager = $player->getName();
                            $type->sendPopup("§c$damager made you bleed!");
                        }
                    }
                }
                    //AXES
                    if($ability == MCPlayer::AXES && $item == 258 || $ability == MCPlayer::AXES && $item == 271 || $ability == MCPlayer::AXES && $item == 275 || $ability == MCPlayer::AXES && $item == 279 || $ability == MCPlayer::AXES && $item == 286) {
                        $damage = $e->getFinalDamage();
                        $type->level->addParticle(new CriticalParticle($type->getLocation()->add(
                            $type->width / 2 + mt_rand(-100, 100) / 500,
                            $type->height + 1 / 2 + mt_rand(-100, 100) / 500,
                            $type->width / 2 + mt_rand(-100, 100) / 500)));
                        if($type instanceof Player) {
                            $type->damageArmor($damage + 10);
                        }
                    }
                }
                
                // UNARMED HITS PLAYER
                if ($type instanceof Player) {
                    $data = $this->plugin->players[strtolower($player->getName())];
                    if($data instanceof MCPlayer) {
                        if($player->getItemInHand()->getName() == "Air") {
                            $chance = $data->stats[MCPlayer::UNARMED]["level"] * .25;
                            if(mt_rand(0, 100) <= $chance) {
                                $item = $type->getInventory()->getItemInHand();
                            }
                            $data->addExp(MCPlayer::UNARMED, 35);
                            return;
                        }
                        if($item == 267 || $item == 268 || $item == 272 || $item == 276 || $item == 283) {
                            $data->addExp(MCPlayer::SWORDS, 50);
                            return;
                        }
                        if($item == 258 || $item == 271 || $item == 275 || $item == 279 || $item == 286) {
                            $data->addExp(MCPlayer::AXES, 50);
                            return;
                        }
                    }
                }
                // UNARMED HITS MONSTER
                if ($type instanceof Monster || $type instanceof Animal) {
                    $data = $this->plugin->players[strtolower($player->getName())];
                    if($data instanceof MCPlayer) {
                        if($player->getInventory()->getItemInHand()->getName() == "Air") {
                            $data->addExp(MCPlayer::UNARMED, 150);
                            return;
                        }
                        if($item == 268 || $item == 272 || $item == 276 || $item == 283) {
                            $data->addExp(MCPlayer::SWORDS, 200);
                            return;
                        }
                        if($item == 258 || $item == 271 || $item == 275 || $item == 279 || $item == 286) {
                            $data->addExp(MCPlayer::AXES, 20);
                            return;
                        }
                    }
                }
              }
            }
        }

    /*
     * @param BlockBreakEvent $e
     *
     * @priority LOWEST
     */
    public function onBreak(BlockBreakEvent $e) {
        if($e->isCancelled()) {
            return;
        }
        if(!$e->isCancelled()) {
            $player = $e->getPlayer();
            $hand = $player->getInventory()->getItemInHand()->getId();
            $data = $this->plugin->players[strtolower($player->getName())];
            $broke = $e->getBlock()->getId();
            if($e->getBlock()->getId() == 17 || $e->getBlock()->getId() == 18) {
                $data->addExp(MCPlayer::WOOD, 30);
                if($hand == 258 || $hand == 271 || $hand == 275 || $hand == 279 || $hand == 286) {
                    $data->addExp(MCPlayer::AXES, 20);
                }
                $chance = $data->stats[MCPlayer::WOOD]["level"] / 3;
                if (mt_rand(0, 1) <= $chance && $broke == 17) {
                    $item = new Item(17);
                    $drops = $e->getDrops();
                    $drops[] = $item;
                    $e->setDrops($drops);
                }
                if (mt_rand(0, 1000) <= $chance && $broke == 18) {
                    $item = new Item(322);
                    $drops = $e->getDrops();
                    $drops[] = $item;
                    $e->setDrops($drops);
                }
                if (mt_rand(0, 4000) <= $chance / 3 && $broke == 18) {
                    $item = new Item(466);
                    $drops = $e->getDrops();
                    $drops[] = $item;
                    $e->setDrops($drops);
                }
            }
            if($hand == 258 || $hand == 270 || $hand == 274 || $hand == 278 || $hand == 285) {
                $data->addExp(MCPlayer::MINING, 30);
                $chance = $data->stats[MCPlayer::MINING]["level"] / 3;
                if ($broke == 1 || $broke == 14 || $broke == 15 || $broke == 16 || $broke == 21 || $broke == 56 || $broke == 73 || $broke == 129) {
                    if (mt_rand(0, 1000) <= $chance) {
                        $item = new Item(265);
                        $drops = $e->getDrops();
                        $drops[] = $item;
                        $e->setDrops($drops);
                    }
                    if (mt_rand(0, 4000) <= $chance / 2) {
                        $item = new Item(264);
                        $drops = $e->getDrops();
                        $drops[] = $item;
                        $e->setDrops($drops);
                    }
                    if (mt_rand(0, 10000) <= $chance / 2) {
                        $item = Item::get(52, 0, 1);
                        $player->getInventory()->addItem($item);
                        $player->sendPopup("§aYou found a Mob Spawner!");
                    }
                }
            }
            if($hand == 256 || $hand == 269 || $hand == 273 || $hand == 277 || $hand == 284) {
                $data->addExp(MCPlayer::DIG, 50);
                $chance = $data->stats[MCPlayer::DIG]["level"] / 3;
                if (mt_rand(0, 800) <= $chance && ($broke == 3 || $broke == 2 || $broke == 12 || $broke == 13)) {
                    $item = new Item(89);
                    $drops = $e->getDrops();
                    $drops[] = $item;
                    $e->setDrops($drops);
                }
                if (mt_rand(0, 1000) <= $chance / 2 && ($broke == 3 || $broke == 2 || $broke == 12 || $broke == 13)) {
                    $item = new Item(264);
                    $drops = $e->getDrops();
                    $drops[] = $item;
                    $e->setDrops($drops);
                }
            }
            if($e->getBlock() instanceof Crops) {
                $data->addExp(MCPlayer::HERB, 75);
            }
            if(isset($this->plugin->ability[strtolower($player->getName())])) {
                $ability = $this->plugin->ability[strtolower($player->getName())]["ability"];
                $data = $this->plugin->players[strtolower($player->getName())];
                $item = $player->getInventory()->getItemInHand()->getId();
                $broke = $e->getBlock()->getId();
                //HERB
                if ($ability == MCPlayer::HERB && $e->getBlock() instanceof Crops) {
                    foreach($e->getDrops() as $drop) {
                        $chance = $data->stats[MCPlayer::HERB]["level"];
                        if (mt_rand(0, 400) <= $chance) {
                            $drop->setCount($drop->count * 2);
                        }
                    }
                }
                //DIG
                if ($ability == MCPlayer::DIG) {
                    $e->setInstaBreak(true);
                }
                //MINING
                if ($ability == MCPlayer::MINING) {
                    $e->setInstaBreak(true);
                    foreach($e->getDrops() as $drop) { 
                        if (mt_rand(0, 100) <= 1 && $broke == 1) {
                            $drop->setCount($drop->count * 2);
                        }
                    }
                }
            }
        }
    }

    public function onPlace(BlockPlaceEvent $e) {
        if($e->isCancelled()) {
            return;
        }
        $player = $e->getPlayer();
        if(isset($this->plugin->ability[strtolower($player->getName())])) {
            $ability = $this->plugin->ability[strtolower($player->getName())]["ability"];
            $data = $this->plugin->players[strtolower($player->getName())];
            $item = $player->getInventory()->getItemInHand()->getId();
            $broke = $e->getBlock()->getId();
            //HERB
            if ($ability == MCPlayer::HERB && $item == 361 || $ability == MCPlayer::HERB && $item == 295 || $ability == MCPlayer::HERB && $item == 362 || $ability == MCPlayer::HERB && $item == 338) {
                $data->addExp(MCPlayer::HERB, 70);

            }
        }
    }

    public function onTap(PlayerInteractEvent $e) {
        $player = $e->getPlayer();
        if(isset($this->plugin->tap[$player->getName()])) {
            if($player->getInventory()->getItemInHand()->getName() == "Air") {
                if(isset($this->plugin->players[strtolower($player->getName())])) {
                    $data = $this->plugin->players[strtolower($player->getName())];
                    if($data instanceof MCPlayer) {
                        if(isset($data->cooldowns[MCPlayer::UNARMED])) {
                            $this->plugin->checkTime($data, MCPlayer::UNARMED);
                        }
                        if(!isset($data->cooldowns[MCPlayer::UNARMED])) {
                            $data->cooldowns[MCPlayer::UNARMED] = time() + 60 * 15;
                            $this->plugin->ability[strtolower($player->getName())]["time"] = $this->getTime($data->stats[MCPlayer::UNARMED]["level"]);
                            if($data->stats[MCPlayer::UNARMED]["level"] <= 3) {
                                $this->plugin->ability[strtolower($player->getName())]["time"] = time() + 1;
                            }
                            $this->plugin->ability[strtolower($player->getName())]["pp"] = $data;
                            $this->plugin->ability[strtolower($player->getName())]["ability"] = MCPlayer::UNARMED;
                            $time = $data->stats[MCPlayer::UNARMED]["level"] / 5 + 3; 
                            $player->addEffect(new EffectInstance(Effect::getEffect(Effect::SPEED), $time * 20, 1));
                            $player->addEffect(new EffectInstance(Effect::getEffect(Effect::STRENGTH), $time * 20, 0));
                            $player->sendPopup($this->plugin->colorize("&a&lBerserk Activated!&r"));
                            return;
                        }
                    }
                }
            }
            if($player->getInventory()->getItemInHand()->getId() >= 290 && $player->getInventory()->getItemInHand()->getId() <= 294) {
                if(isset($this->plugin->players[strtolower($player->getName())])) {
                    $data = $this->plugin->players[strtolower($player->getName())];
                    if($data instanceof MCPlayer) {
                        if(isset($data->cooldowns[MCPlayer::HERB])) {
                            $this->plugin->checkTime($data, MCPlayer::HERB);
                        }
                        if(!isset($data->cooldowns[MCPlayer::HERB])) {
                            $data->cooldowns[MCPlayer::HERB] = time() + 10 * 15;
                            $this->plugin->ability[strtolower($player->getName())]["time"] = $this->getTime($data->stats[MCPlayer::HERB]["level"]);
                            if($data->stats[MCPlayer::HERB]["level"] == 0) {
                                $this->plugin->ability[strtolower($player->getName())]["time"] = time() + 1;
                            }
                            $this->plugin->ability[strtolower($player->getName())]["pp"] = $data;
                            $this->plugin->ability[strtolower($player->getName())]["ability"] = MCPlayer::HERB;
                            $player->sendPopup($this->plugin->colorize("&a&lGreen Terra Activated!&r"));
                            return;
                        }
                    }
                }
            }
            $item = $player->getInventory()->getItemInHand()->getId();
            if($item == 258 || $item == 271 || $item == 275 || $item == 279 || $item == 286) {
                if(isset($this->plugin->players[strtolower($player->getName())])) {
                    $data = $this->plugin->players[strtolower($player->getName())];
                    if($data instanceof MCPlayer) {
                        if(isset($data->cooldowns[MCPlayer::AXES])) {
                            $this->plugin->checkTime($data, MCPlayer::AXES);
                        }
                        if(!isset($data->cooldowns[MCPlayer::AXES])) {
                            $data->cooldowns[MCPlayer::AXES] = time() + 15 * 15;
                            $this->plugin->ability[strtolower($player->getName())]["time"] = $this->getTime($data->stats[MCPlayer::AXES]["level"]);
                            if($data->stats[MCPlayer::AXES]["level"] == 0) {
                                $this->plugin->ability[strtolower($player->getName())]["time"] = time() + 1;
                            }
                            $this->plugin->ability[strtolower($player->getName())]["pp"] = $data;
                            $this->plugin->ability[strtolower($player->getName())]["ability"] = MCPlayer::AXES;
                       $time = $data->stats[MCPlayer::AXES]["level"] / 5 + 4; 
                             $player->addEffect(new EffectInstance(Effect::getEffect(Effect::STRENGTH), $time * 20, 0));
                            $player->sendPopup($this->plugin->colorize("&a&lSkull Splitter Activated!&r"));
                            return;
                        }
                    }
                }
            }
            if($item == 256 || $item == 269 || $item == 273 || $item == 277 || $item == 284) {
                if(isset($this->plugin->players[strtolower($player->getName())])) {
                    $data = $this->plugin->players[strtolower($player->getName())];
                    if($data instanceof MCPlayer) {

                        if(isset($data->cooldowns[MCPlayer::DIG])) {
                            $this->plugin->checkTime($data, MCPlayer::DIG);
                        }
                        if(!isset($data->cooldowns[MCPlayer::DIG])) {
                            $data->cooldowns[MCPlayer::DIG] = time() + 20 * 15;
                            $this->plugin->ability[strtolower($player->getName())]["time"] = $this->getTime($data->stats[MCPlayer::DIG]["level"]);
                            if($data->stats[MCPlayer::DIG]["level"] == 0) {
                                $this->plugin->ability[strtolower($player->getName())]["time"] = time() + 1;
                            }
                            $this->plugin->ability[strtolower($player->getName())]["pp"] = $data;
                            $this->plugin->ability[strtolower($player->getName())]["ability"] = MCPlayer::DIG;
                            $time = $data->stats[MCPlayer::DIG]["level"] / 3 + 4; 
                            $player->addEffect(new EffectInstance(Effect::getEffect(Effect::HASTE), $time * 20, 1));
                            $player->sendPopup($this->plugin->colorize("&a&lGiga Drill Activated!&r"));
                            return;
                        }
                    }
                }
            }
            if($item == 267 || $item == 268 || $item == 272 || $item == 276 || $item == 283) {
                if (isset($this->plugin->players[strtolower($player->getName())])) {
                    $data = $this->plugin->players[strtolower($player->getName())];
                    if ($data instanceof MCPlayer) {
                        if(isset($data->cooldowns[MCPlayer::SWORDS])) {
                            $this->plugin->checkTime($data, MCPlayer::SWORDS);
                        }
                        if (!isset($data->cooldowns[MCPlayer::SWORDS])) {
                            $data->cooldowns[MCPlayer::SWORDS] = time() + 20 * 15;
                            $this->plugin->ability[strtolower($player->getName())]["time"] = $this->getTime($data->stats[MCPlayer::SWORDS]["level"]);
                            if ($data->stats[MCPlayer::SWORDS]["level"] == 0) {
                                $this->plugin->ability[strtolower($player->getName())]["time"] = time() + 1;
                            }
                            $this->plugin->ability[strtolower($player->getName())]["pp"] = $data;
                            $this->plugin->ability[strtolower($player->getName())]["ability"] = MCPlayer::SWORDS;
                            $time = $data->stats[MCPlayer::SWORDS]["level"] / 8 + 3; 
                            $player->addEffect(new EffectInstance(Effect::getEffect(Effect::STRENGTH), $time * 20, 0));
                            $player->sendPopup($this->plugin->colorize("&a&lSerrated Strikes Activated!&r"));
                            return;
                        }
                    }
                }
            }
            if($item == 258 || $item == 270 || $item == 274 || $item == 278 || $item == 285) {
                if(isset($this->plugin->players[strtolower($player->getName())])) {
                    $data = $this->plugin->players[strtolower($player->getName())];
                    if($data instanceof MCPlayer) {
                        if(isset($data->cooldowns[MCPlayer::MINING])) {
                            $this->plugin->checkTime($data, MCPlayer::MINING);
                        }
                        if(!isset($data->cooldowns[MCPlayer::MINING])) {
                            $data->cooldowns[MCPlayer::MINING] = time() + 30 * 15;
                            $this->plugin->ability[strtolower($player->getName())]["time"] = $this->getTime($data->stats[MCPlayer::MINING]["level"]);
                            if($data->stats[MCPlayer::MINING]["level"] == 0) {
                                $this->plugin->ability[strtolower($player->getName())]["time"] = time() + 1;
                            }
                            $this->plugin->ability[strtolower($player->getName())]["pp"] = $data;
                            
                            $this->plugin->ability[strtolower($player->getName())]["ability"] = MCPlayer::MINING;
                            $time = $data->stats[MCPlayer::MINING]["level"] / 10 + 3; 
                             $player->addEffect(new EffectInstance(Effect::getEffect(Effect::HASTE), $time * 20, 2));
                            $player->sendPopup($this->plugin->colorize("&a&lSuper Breaker Activated!&r"));
                            return;
                        }
                    }
                }
            }
        }
        $this->plugin->tap[$player->getName()] = $player;
    }
    
    public function onJoin(PlayerJoinEvent $e) {
        echo 'TEST';
        $player = $e->getPlayer();
        $player->sendMessage("Test");
        if(!$this->plugin->ifPlayerExists($player->getName())) {
            $this->plugin->addPlayer($player->getName());
            $stats = $this->plugin->getStats($player->getName());
            $this->plugin->players[strtolower($player->getName())] = new MCPlayer($this->plugin, $player, $stats);
            return;
        } else {
            $this->plugin->updatePlayerTime($player->getName());
            $stats = $this->plugin->getStats($player->getName());
            $this->plugin->players[strtolower($player->getName())] = new MCPlayer($this->plugin, $player, $stats);
            return;
        }
    }

    public function onQuit(PlayerQuitEvent $e) {
        if(isset($this->plugin->players[strtolower($e->getPlayer()->getName())])) {
            $this->plugin->players[strtolower($e->getPlayer()->getName())]->saveStats();
        }
        unset($this->plugin->players[strtolower($e->getPlayer()->getName())]);
        unset($this->plugin->ability[strtolower($e->getPlayer()->getName())]);
    }

    public function getTime($level) {
        if($level <= 20) {
            return time() + 10;
        }
        if($level <= 35) {
            return time() + 15;
        }
        if($level <= 50) {
            return time() + 20;
        }
        if($level <= 65) {
            return time() + 25;
        }
        if($level <= 80) {
            return time() + 30;
        }
        if($level <= 100) {
            return time() + 35;
        }
        if($level <= 125) {
            return time() + 40;
        }
        if($level <= 150) {
            return time() + 45;
        }
        if($level <= 200) {
            return time() + 50;
        }
        if($level >= 201) {
            return time() + 60;
        }
    }
    
}