<?php

namespace xZeroStorm;

use pocketmine\level\Position;
use pocketmine\inventory\ChestInventory;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\scheduler\Task;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\block\Block;
use pocketmine\level\Level;
use pocketmine\utils\TextFormat;
use pocketmine\item\item;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Double;
use pocketmine\nbt\tag\Enum;
use pocketmine\tile\Tile;
use pocketmine\tile\Chest;
use pocketmine\tile\Sign;

class EG extends PluginBase implements Listener {

    public $Signprefix = TextFormat::DARK_GRAY . "[" . TextFormat::DARK_PURPLE . "EnderGames" . TextFormat::DARK_GRAY . "]";
    public $prefix = TextFormat::DARK_PURPLE . "EnderGames" . TextFormat::DARK_GRAY . " | " . TextFormat::GRAY;
    public $arenas = array();
    public $signregister = false;
    public $temparena = "";

    public function onEnable() {

        @mkdir($this->getDataFolder());

        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        @mkdir($this->getDataFolder());
        @mkdir($this->getDataFolder() . "Maps");
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);

        if ($config->get("arenas") == null) {
            $config->set("arenas", array("EnderGames"));
            $config->save();
        }
        $items = array(
        
            /* Blöcke */
            
            array(1, 0, 8),
            array(3, 0, 8),
            array(4, 0, 8),
            array(5, 0, 8),
            array(18, 0, 8),
            array(20, 0, 8),
            array(24, 0, 8),
            array(45, 0, 8),
            array(46, 0, 8),
            array(87, 0, 8),
            array(121, 0, 8),
            array(168, 0, 8),
            
            array(1, 0, 16),
            array(3, 0, 16),
            array(4, 0, 16),
            array(5, 0, 16),
            array(18, 0, 8),
            array(20, 0, 16),
            array(24, 0, 16),
            array(45, 0, 16),
            array(46, 0, 16),
            array(87, 0, 16),
            array(121, 0, 16),
            array(168, 0, 16),
            
            /* Schwert & Rüstung */
            
            array(267, 0, 1),
            array(268, 0, 1),
            array(272, 0, 1),
            array(276, 0, 1),
            array(283, 0, 1),
            
            array(257, 0, 1),
            array(258, 0, 1),
            array(270, 0, 1),
            array(271, 0, 1),
            array(274, 0, 1),
            array(275, 0, 1),
            array(278, 0, 1),
            array(279, 0, 1),
            array(285, 0, 1),
            array(286, 0, 1),
            array(261, 0, 1),
            array(262, 0, 16),
            
            array(298, 0, 1),
            array(299, 0, 1),
            array(300, 0, 1),
            array(301, 0, 1),
            array(302, 0, 1),
            array(303, 0, 1),
            array(305, 0, 1),
            array(306, 0, 1),
            array(307, 0, 1),
            array(308, 0, 1),
            array(309, 0, 1),
            array(310, 0, 1),
            array(311, 0, 1),
            array(312, 0, 1),
            array(313, 0, 1),
            array(314, 0, 1),
            array(315, 0, 1),
            array(316, 0, 1),
            array(317, 0, 1),
            array(346, 0, 1),
            
            /* Items */
            
            array(30, 0, 7),
            array(264, 0, 2),
            array(326, 0, 1),
            array(327, 0, 1),
            array(368, 0, 1),
            array(373, 11, 1),
            array(373, 16, 1),
            array(373, 30, 1),
            array(373, 33, 1),
            array(444, 0, 1),
            array(438, 11, 1),
            array(438, 16, 1),
            array(438, 22, 1),
            array(438, 30, 1),
            array(438, 33, 1),
            array(438, 35, 1),
            array(438, 23, 1),
            array(438, 24, 1),
            
            array(344, 0, 8),
            array(345, 0, 1),
            array(357, 0, 8),
            array(360, 0, 8),
            array(363, 0, 8),
            array(364, 0, 8),
            array(365, 0, 8),
            array(366, 0, 8),
            array(367, 0, 8),
            array(391, 0, 8),
            array(392, 0, 8),
            array(393, 0, 8),
            array(385, 0, 2),
            array(394, 0, 8),
            array(351, 4, 16),
            array(351, 4, 8),
            array(351, 4, 32),
            array(384, 0, 16),
            array(384, 0, 8),
            array(384, 0, 32)
        );
        if ($config->get("chestitems") == null) {
            $config->set("chestitems", $items);
            $config->save();
        }
        $this->arenas = $config->get("arenas");
        foreach ($this->arenas as $arena) {
            $this->resetArena($arena);
            if (file_exists($this->getServer()->getDataPath() . "worlds/" . $arena)) {
            	   $this->copymap($this->getDataFolder() . "Maps/" . $arena, $this->getServer()->getDataPath() . "worlds/" . $arena);
                $this->getLogger()->Info($this->prefix . "Arena " . $arena . " wurde geladen");
                $this->getServer()->loadLevel($arena);
            }
        }

        $this->getScheduler()->scheduleRepeatingTask(new ESGameSender($this), 20);
        $this->getScheduler()->scheduleRepeatingTask(new ESRefreshSigns($this), 20);
        $this->getLogger()->info($this->prefix . "wurde erfolgreich von xZeroStorm geladen!");
    }
    	
    public function resetArena($arena) {
        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $level = $this->getServer()->getLevelByName($arena);
        if ($level instanceof Level) {
            $this->getServer()->unloadLevel($level);
            $this->getServer()->loadLevel($arena);
        }
        $config->set($arena . "LobbyTimer", 31);
        $config->set($arena . "EndTimer", 16);
        $config->set($arena . "GameTimer", 10 * 60 + 1);
        $config->set($arena . "Status", "Lobby");
        $config->save();
    }

    public function onBreak(BlockBreakEvent $event) {
        $player = $event->getPlayer();

        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $welt = $player->getLevel()->getFolderName();

        if (in_array($welt, $this->arenas)) {
            $status = $config->get($welt . "Status");

            if ($status == "Lobby") {
                $event->setCancelled(TRUE);
                
            }
        }
    }

    public function onPlace(BlockPlaceEvent $event) {
        $player = $event->getPlayer();

        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $welt = $player->getLevel()->getFolderName();

        if (in_array($welt, $this->arenas)) {
            $status = $config->get($welt . "Status");

            if ($status == "Lobby") {
                $event->setCancelled(TRUE);
            }
        }
    }

    public function onHit(EntityDamageEvent $event) {

        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        if ($event->getEntity() instanceof Player) {
            $entity = $event->getEntity();

            if (in_array($event->getEntity()->getLevel()->getFolderName(), $this->arenas)) {
                if ($config->get($event->getEntity()->getLevel()->getFolderName() . "Status") == "Lobby") {
                    $event->setCancelled();
                }
            }

            if ($event instanceof EntityDamageByEntityEvent) {

                if ($event->getEntity() instanceof Player && $event->getDamager() instanceof Player) {

                    $victim = $event->getEntity();
                    $status = "-";
                    $damager = $event->getDamager();

                    if (in_array($event->getEntity()->getLevel()->getFolderName(), $this->arenas)) {
                        if ($config->get($victim->getLevel()->getFolderName() . "Status") == "Lobby") {
                            $event->setCancelled();
                            
                        }
                    }
                }
            }
        }
    }

    public function onQuit(PlayerQuitEvent $event) {

        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $playerE = $event->getPlayer();
        $nameE = $playerE->getName();
        $playerE->removeAllEffects();
        $welt = $playerE->getLevel()->getFolderName();

        $status = "-";
        $maxplayers = "-";


        if (in_array($welt, $this->arenas)) {
            $status = $config->get($welt . "Status");
            $maxplayers = $config->get($welt . "Spieleranzahl");
        }

        if (in_array($playerE->getLevel()->getFolderName(), $this->arenas)) {

            foreach ($playerE->getLevel()->getPlayers() as $p) {
                $player = $p;

                if ($status != "Lobby") {
                    $aliveplayers = count($this->getServer()->getLevelByName($welt)->getPlayers());
                    $aliveplayers--;
                    $maxplayers = $config->get($welt . "Spieleranzahl");
                }
            }
        }
    }

    public function onDeath(PlayerDeathEvent $event) {

        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $playerE = $event->getEntity();
        $playerE->removeAllEffects();
        $nameE = $playerE->getName();
        $welt = $playerE->getLevel()->getFolderName();

        $status = "-";
        $maxplayers = "-";
	 
        if (in_array($welt, $this->arenas)) {
            $status = $config->get($welt . "Status");
            {
                $maxplayers = $config->get($welt . "Spieleranzahl");
            }

            if (in_array($playerE->getLevel()->getFolderName(), $this->arenas)) {

                foreach ($playerE->getLevel()->getPlayers() as $p) {
                    $player = $p;

                    if ($status == "Lobby") {
                        
                    } else {
                        $aliveplayers = count($this->getServer()->getLevelByName($welt)->getPlayers());
                        $aliveplayers--;
                    }
                }
            }
        }
    }

    public function copymap($src, $dst) {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ( $file = readdir($dir))) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if (is_dir($src . '/' . $file)) {
                    $this->copymap($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    public function onInteract(PlayerInteractEvent $event) {

        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $itemID = $event->getPlayer()->getInventory()->getItemInHand()->getID();
        $block = $event->getBlock();
        $chest = $event->getPlayer()->getLevel()->getTile($event->getBlock());
        $blockID = $block->getID();
        $player = $event->getPlayer();
        $arena = $player->getLevel()->getFolderName();
        $tile = $player->getLevel()->getTile($block);
        
        $welt = $player->getLevel()->getFolderName();

        if (in_array($welt, $this->arenas)) {
            $status = $config->get($welt . "Status");

            if ($status == "Lobby") {
                $event->setCancelled(TRUE);
                
            }
        }
        
        if ($tile instanceof Sign) {

            if ($this->signregister === true && $this->signregisterWHO == $player->getName()) {
                $tile->setText($this->Signprefix, $this->temparena, TextFormat::GRAY . "[" . TextFormat::YELLOW . "Lade..." . TextFormat::GRAY . "]", "");
                $this->signregister = false;
            }

            $text = $tile->getText();
            if ($text[0] == $this->Signprefix) {
                if ($text[2] == TextFormat::GRAY . "[" . TextFormat::GREEN . "Betreten" . TextFormat::GRAY . "]") {
                    $spieleranzahl = count($this->getServer()->getLevelByName($text[1])->getPlayers());
                    $maxplayers = $config->get($text[1] . "Spieleranzahl");
                    if ($spieleranzahl < $maxplayers) {
                        $level = $this->getServer()->getLevelByName($text[1]);
                        $spawn = $level->getSafeSpawn();
                        $level->loadChunk($spawn->getX(), $spawn->getZ());
                        $player->teleport($spawn, 0, 0);
                        $player->getInventory()->clearAll();
                        $player->removeAllEffects();
                        $player->setFood(20);
                        $player->setHealth(20);
                    } else {
                        $player->sendMessage($this->prefix . "Arena " . $text[1] . " ist voll!");
                    }
                } else {
                    $player->sendMessage($this->prefix . "Du kannst diese Runde nicht betreten!");
                }
            }
        }
    }

    public function fillChests(Level $level) {
        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $tiles = $level->getTiles();
        foreach ($tiles as $t) {
            if ($t instanceof Chest) {
                $chest = $t;
                $chest->getInventory()->clearAll();
                if ($chest->getInventory() instanceof ChestInventory) {
                    for ($i = 0; $i <= 26; $i++) {
                        $rand = rand(1, 3);
                        if ($rand == 1) {
                            $k = array_rand($config->get("chestitems"));
                            $v = $config->get("chestitems")[$k];
                            $chest->getInventory()->setItem($i, Item::get($v[0], $v[1], $v[2]));
                        }
                    }
                }
            }
        }
    }

    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool {
        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);

        $arena = $sender->getLevel()->getFolderName();
        
        
        if (in_array($arena, $this->arenas)) {
            $status = $config->get($arena . "Status");
        } else {
            $status = "NO-ARENA";
        }
        
        if ($cmd->getName() == "spawn") {
            $sender->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn());
            $sender->setFood(20);
            $sender->setHealth(20);
            $sender->getInventory()->clearAll();
            $sender->getArmorInventory()->clearAll();
            $sender->removeAllEffects();
        }
        
        if ($cmd->getName() == "lobby") {
            $sender->transfer("", "19132");
        }
        
        if ($cmd->getName() == "endergames") {
            if (!empty($args[0])) {
                if ($args[0] == "addarena" && $sender->isOP()) {
                    if (!empty($args[1]) && !empty($args[2])) {
                        if (file_exists($this->getServer()->getDataPath() . "worlds/" . $args[1])) {
                            $arena = $args[1];
                            $this->arenas[] = $arena;
                            $config->set("arenas", $this->arenas);
                            $config->set($arena . "Spieleranzahl", (int) $args[2]);
                            $config->save();
                            $this->copymap($this->getServer()->getDataPath() . "worlds/" . $arena, $this->getDataFolder() . "Maps/" . $arena);
                            $this->resetArena($arena);
                            $sender->sendMessage($this->prefix . "Du hast erfolgreich eine neue EnderGames Arena erstellt!");
                        }
                    }
                } elseif ($args[0] == "refill" && $sender->isOP()) {
                    $this->fillChests($this->getServer()->getLevelByName($sender->getLevel()->getFolderName()));
                    $sender->sendMessage($this->prefix . "Alle Kisten auf der Map " . $sender->getLevel()->getFolderName() . " wurden befüllt");
                } elseif ($args[0] == "regsign" && $sender->isOP()) {
                    if (!empty($args[1])) {

                        $this->signregister = true;
                        $this->signregisterWHO = $sender->getName();
                        $this->temparena = $args[1];

                        $sender->sendMessage($this->prefix . "Tippe nun ein Schild an um die Runde zu Registrieren");
                    }
                } elseif ($args[0] == "start" && $sender->hasPermission("es.forcestart")) {
                    $arena = $sender->getLevel()->getFolderName();
					if(in_array($arena, $this->arenas)){
						
						if($config->get($arena."Status") == "Lobby"){
							$config->set($arena."LobbyTimer", 6);
							$config->save();
							$sender->sendMessage($this->prefix . "ForceStart wurde Aktiviert!");
						}
						
					} else {
						$sender->sendMessage($this->prefix . TextFormat::RED . "Du bist derzeit in keiner EnderGames Arena drinne!");
				}
					
                } elseif ($args[0] == "setspawn" && $sender->isOP()) {
                    if (!empty($args[1])) {
                        $arena = $sender->getLevel()->getFolderName();
                        $x = $sender->getX();
                        $y = $sender->getY();
                        $z = $sender->getZ();
                        $coords = array($x, $y, $z);

                        $config->set($arena . "Spawn" . $args[1], $coords);
                        $config->save();
                        $sender->sendMessage($this->prefix . "Du hast Spawn " . $args[1] . " der Runde gesetzt!");
                    }
                }
            }
        }
        return false;
    }
}

class ESRefreshSigns extends Task {

    public $prefix = "";
    public $Signprefix = "";

    public function __construct($plugin) {
        $this->plugin = $plugin;
        $this->prefix = $this->plugin->prefix;
        $this->Signprefix = $this->plugin->Signprefix;
    }

    public function onRun($tick) {
        $allplayers = $this->plugin->getServer()->getOnlinePlayers();
        $level = $this->plugin->getServer()->getDefaultLevel();
        $tiles = $level->getTiles();
        foreach ($tiles as $t) {
            if ($t instanceof Sign) {
                $text = $t->getText();
                if ($text[0] == $this->Signprefix) {
                    $aop = count($this->plugin->getServer()->getLevelByName($text[1])->getPlayers();
                    $ingame = TextFormat::GRAY . "[" . TextFormat::GREEN . "Betreten" . TextFormat::GRAY . "]";
                    $config = new Config($this->plugin->getDataFolder() . "config.yml", Config::YAML);
                    $count = $config->get($text[1] . "Spieleranzahl");
                    if ($config->get($text[1] . "Status") != "Lobby") {
                        $ingame = TextFormat::GRAY . "[" . TextFormat::RED . "InGame" . TextFormat::GRAY . "]";
                    }
                    if ($aop >= 24) {
                        $ingame = TextFormat::GRAY . "[" . TextFormat::DARK_RED . "Voll" . TextFormat::GRAY . "]";
                    }
                    if ($config->get($text[1] . "Status") == "Ende") {
                        $ingame = TextFormat::GRAY . "[" . TextFormat::YELLOW . "Restartet..." . TextFormat::GRAY . "]";
                    }
                    $t->setText($this->Signprefix, $text[1], $ingame, TextFormat::GREEN . $aop . TextFormat::GRAY . " / " . TextFormat::RED . $count);
                }
            }
        }
    }

}

class ESGameSender extends Task {

    public $prefix = "";
    public $Signprefix = "";
    
    public function __construct($plugin) {
        $this->plugin = $plugin;
        $this->prefix = $this->plugin->prefix;
        $this->Signprefix = $this->plugin->Signprefix;
        
    }

    public function onRun($tick) {
        $config = new Config($this->plugin->getDataFolder() . "config.yml", Config::YAML);
        $arenas = $config->get("arenas");
        if (count($arenas) != 0) {
            foreach ($arenas as $arena) {
                $status = $config->get($arena . "Status");
                $lobbytimer = $config->get($arena . "LobbyTimer");
                $endtimer = $config->get($arena . "EndTimer");
                $gametimer = $config->get($arena . "GameTimer");
                $levelArena = $this->plugin->getServer()->getLevelByName($arena);
                if ($levelArena instanceof Level) {
                    $players = $levelArena->getPlayers();


                    if ($status == "Lobby") {

                        if (count($players) < 1) {
                            $config->set($arena . "LobbyTimer", 31);
                            $config->set($arena . "EndTimer", 16);
                            $config->set($arena . "Status", "Lobby");
                            $config->save();

                        } else {

                            $lobbytimer--;
                            $config->set($arena . "LobbyTimer", $lobbytimer);
                            $config->save();

                            if ($lobbytimer == 30 ||
                                    $lobbytimer == 25 ||
                                    $lobbytimer == 20 ||
                                    $lobbytimer == 15 ||
                                    $lobbytimer == 14 ||
                                    $lobbytimer == 13 ||
                                    $lobbytimer == 12 ||
                                    $lobbytimer == 11           
                                    ) {
                                foreach ($players as $p) {
                                    $p->addTitle("", TextFormat::GREEN . "EnderGames startet in:" $lobbytimer);
                                }
                            }
                            
                            if ($lobbytimer >= 5 && $lobbytimer <= 10) {
                                foreach ($players as $p) {
                                    $p->addTitle("", TextFormat::GREEN . $lobbytimer);
                                    $this->plugin->fillChests($levelArena);
                                }
                            }
                            if ($lobbytimer >= 1 && $lobbytimer <= 5) {
                                foreach ($players as $p) {
                                    $p->addTitle("", TextFormat::RED . $lobbytimer);
                                    $this->plugin->fillChests($levelArena);
                                }
                            }
                            if ($lobbytimer <= 0) {

                                $countPlayers = 0;

                                foreach ($players as $p) {
                                    $countPlayers++;

                                    $spawn = $config->get($arena . "Spawn" . $countPlayers);
                                    $p->teleport(new Vector3($spawn[0], $spawn[1], $spawn[2]));
                                    $p->setFood(20);
                                    $p->setHealth(20);
                                    $p->getInventory()->clearAll();
                                    $p->getArmorInventory()->clearAll();
                                    $p->removeAllEffects();

                                    $this->plugin->fillChests($levelArena);
                                }

                                $config->set($arena . "Status", "InGame");
                                $config->save();
                            }
                        }
                    }
                    if ($status == "InGame") {

                        $gametimer--;
                        $config->set($arena . "GameTimer", $gametimer);
                        $config->save();

                        $min = $gametimer / 60;

                        if ($gametimer == 10 * 60 ||
                        	    $gametimer == 5 * 60 ||
                                $gametimer == 4 * 60 ||
                                $gametimer == 3 * 60 ||
                                $gametimer == 120 ||
                                $gametimer == 60
                        ) {
                            foreach ($players as $p) {
                                //$p->sendMessage($this->prefix . "Runde endet in " . $min . " Minuten!");
                                $this->plugin->fillChests($levelArena);
                            }
                        }
                        if ($gametimer == 30 ||
                                $gametimer == 20 ||
                                $gametimer == 10 ||
                                $gametimer == 5 ||
                                $gametimer == 4 ||
                                $gametimer == 3 ||
                                $gametimer == 2 ||
                                $gametimer == 1
                        ) {
                            foreach ($players as $p) {
                                //$p->sendMessage($this->prefix . "Runde endet in " . TextFormat::GOLD . $gametimer . TextFormat::WHITE . " Sekunden!");
                            }
                        }
                        if ($gametimer == 0) {
                            $config->set($arena . "Status", "Ende");
                            $config->save();
                        }

                        if (count($players) <= 1) {
                            foreach ($players as $p) {
                            
                            }
                            $config->set($arena . "Status", "Ende");
                            $config->save();
                        }
                    }
                    if ($status == "Ende") {

                        if ($endtimer >= 0) {
                            $endtimer--;
                            $config->set($arena . "EndTimer", $endtimer);
                            $config->save();

                            if ($endtimer == 15
                            ) {

                                foreach ($players as $p) {

                                    $p->addTitle("", TextFormat::GREEN . $endtimer);
                                }
                            }
				
				if ($endtimer == 10
                            ) {

                                foreach ($players as $p) {

                                    $p->addTitle("", TextFormat::RED . $endtimer);
                                }
                            }
                            if ($endtimer == 5 ||
                                    $endtimer == 4 ||
                                    $endtimer == 3 ||
                                    $endtimer == 2 ||
                                    $endtimer == 1
                            ) {

                                foreach ($players as $p) {

                                    $p->addTitle("", TextFormat::RED . $endtimer);
                                }
                            }
                            if ($endtimer == 0) {

                                $config = new Config($this->plugin->getDataFolder() . "config.yml", Config::YAML);

                                foreach ($players as $p) {
                                    $p->teleport($this->plugin->getServer()->getDefaultLevel()->getSafeSpawn());
                                    $p->setFood(20);
                                    $p->setHealth(20);
                                    $p->getInventory()->clearAll();
                                    $p->getArmorInventory()->clearAll();
                                    $p->removeAllEffects();
                                }

                                $this->plugin->getServer()->unloadLevel($levelArena);
                                $this->plugin->copymap($this->plugin->getDataFolder() . "Maps/" . $arena, $this->plugin->getServer()->getDataPath() . "worlds/" . $arena);
                                $this->plugin->getServer()->loadLevel($arena);

                                $config->set($arena . "LobbyTimer", 31);
                                $config->set($arena . "GameTimer", 10 * 60 + 1);
                                $config->set($arena . "EndTimer", 16);
                                $config->set($arena . "Status", "Lobby");
                                $config->save();
                            }
                        }
                    }
                }
            }
        }
    }

}
