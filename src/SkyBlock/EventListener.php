<?php
namespace SkyBlock;

use pocketmine\block\Lava;
use pocketmine\block\Water;
use pocketmine\event\block\BlockUpdateEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\event\Listener;
use pocketmine\event\level\LevelLoadEvent;
use pocketmine\event\level\LevelUnloadEvent;
use pocketmine\utils\Config;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\block\ItemFrameDropItemEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\entity\Painting;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\Player;
use pocketmine\event\player\PlayerBucketEmptyEvent;
use pocketmine\event\player\PlayerBucketFillEvent;
use pocketmine\event\player\PlayerBucketEvent;

class EventListener implements Listener
{
	/** @var SkyBlock */
	private $plugin;

	public function __construct(SkyBlock $plugin){
		$this->plugin = $plugin;
	}

	public function onLevelLoad(LevelLoadEvent $event) {
		if ($event->getLevel()->getProvider()->getGenerator() === "skyblock") {
			$settings = $event->getLevel()->getProvider()->getGeneratorOptions();
			if (isset($settings["preset"]) === false or $settings["preset"] === "") {
				return;
			}
			$settings = json_decode($settings["preset"], true);
			if ($settings === false) {
				return;
			}
			$levelName = $event->getLevel()->getName();
			$filePath = $this->plugin->getDataFolder() . "worlds/" . $levelName . ".yml";
			$config = $this->plugin->getConfig();
			$default = [
				"MaxPlotsPerPlayer" => $config->getNested("DefaultWorld.MaxPlotsPerPlayer"),
				"ClaimPrice" => $config->getNested("DefaultWorld.ClaimPrice"),
				"ClearPrice" => $config->getNested("DefaultWorld.ClearPrice"),
				"DisposePrice" => $config->getNested("DefaultWorld.DisposePrice"),
				"ResetPrice" => $config->getNested("DefaultWorld.ResetPrice"),
			];
			$config = new Config($filePath, Config::YAML, $default);
			foreach (array_keys($default) as $key) {
				$settings[$key] = $config->get($key);
			}
			$this->plugin->addLevelSettings($levelName, new PlotLevelSettings($levelName, $settings));
		}
	}

	public function onLevelUnload(LevelUnloadEvent $event) {
		$levelName = $event->getLevel()->getName();
		$this->plugin->unloadLevelSettings($levelName);
	}

	public function onBlockPlace(BlockPlaceEvent $event) {
		$this->onEventOnBlock($event);
	}

	public function onBlockBreak(BlockBreakEvent $event) {
		$this->onEventOnBlock($event);
	}

	public function onPlayerInteract(PlayerInteractEvent $event) {
		$this->onEventOnBlock($event);
	}
	
	private $playerLocationTracker = array();
	
	public function onPlayerMove(PlayerMoveEvent $event) {
	$player = $event->getPlayer();
	$pname = strtolower($player->getName());
	$levelName = $player->getLevel()->getName();
	$players_in_tracker = isset($this->playerLocationTracker[$pname]);
	
		if (!$this->plugin->isLevelLoaded($levelName)) {
		if($players_in_tracker) {
		unset( $this->playerLocationTracker[$pname] );
		}
			return;
		}
		$plot = $this->plugin->getPlotByPosition($player->getPosition());
		
		
		// if users not in a plot make sure unset
		if( is_null($plot) && $players_in_tracker ) {
		unset( $this->playerLocationTracker[$pname] );
		return;
		}
		if( is_null($plot) ) {
		return;
		}
		
		// if user was not previously tracked then save pos and return
		if( ! $players_in_tracker && !is_null($plot)) {
		$this->playerLocationTracker[$pname] = $plot;
		$this->plugin->getServer()->dispatchCommand($player, "p info summary");
		return;
		}
		
		// if user has moved
		if( ( $plot->X != $this->playerLocationTracker[$pname]->X ) || ( $plot->Z != $this->playerLocationTracker[$pname]->Z ) ) {
			$this->playerLocationTracker[$pname] = $plot;
		$this->plugin->getServer()->dispatchCommand($player, "p info summary");
		return;
		}
	}

	public function onBlockUpdate(BlockUpdateEvent $event) {
		/*
		 * Disables water and lava flow as a temporary solution.
		 */

		$levelName = $event->getBlock()->getLevel()->getName();
		if ($this->plugin->isLevelLoaded($levelName)) {
			$event->setCancelled(true);
		}
		if ($event->getBlock() instanceof Water or $event->getBlock() instanceof Lava) {
			$event->setCancelled(true);
		}
	}

	public function onExplosion(EntityExplodeEvent $event) {
		$levelName = $event->getEntity()->getLevel()->getName();
		if ($this->plugin->isLevelLoaded($levelName)) {
			$event->setCancelled(true);
		}

		/* Allow explosions but only break blocks inside the plot the tnt is in.
		 * Disabled due to tnt cannons not being stopped

		$levelName = $event->getEntity()->getLevel()->getName();
		if (!$this->plugin->isLevelLoaded($levelName)) {
			return;
		}
		$plot = $this->plugin->getPlotByPosition($event->getPosition());
		if ($plot === null) {
			$event->setCancelled(true);
			return;
		}
		$beginPos = $this->plugin->getPlotPosition($plot);
		$endPos = clone $beginPos;
		$plotSize = $this->plugin->getLevelSettings($levelName)->plotSize;
		$endPos->x += $plotSize;
		$endPos->z += $plotSize;
		$blocks = array_filter($event->getBlockList(), function($block) use($beginPos, $endPos) {
			if ($block->x >= $beginPos->x and $block->z >= $beginPos->z and $block->x < $endPos->x and $block->z < $endPos->z) {
				return true;
			}
			return false;
		});
		$event->setBlockList($blocks);
		*/
	}

	/**
	 * @param BlockPlaceEvent|BlockBreakEvent|PlayerInteractEvent $event
	 */
	private function onEventOnBlock($event) {
		$levelName = $event->getBlock()->getLevel()->getName();
		if (!$this->plugin->isLevelLoaded($levelName)) {
			return;
		}
		
		$block = $event->getBlock();
		if($block->x==0 && $block->y == 0 && $block->z == 0 ) {
			// not sure what fires these events but they happen all the
			// time. Lets cancel the event and ignore them!
			$event->setCancelled(true);
			return;
		}
		
		$plot = $this->plugin->getPlotByPosition($block);
		
		if ($plot == null) {
			// this should never get called because plots have been
			// extended to include the road around in this particular 
			// fork of SkyBlock
			if(!$event->getPlayer()->hasPermission("skyblock.admin.build.road")) $event->setCancelled(true);
			return;
		}
		
		$username = $event->getPlayer()->getName();

		$hasRights = (($plot->owner == $username || $plot->isHelper($username) ) && !$plot->locked);
		$hasAdmin = (!$plot->locked && $event->getPlayer()->hasPermission("skyblock.admin.build.plot"));
		// even admins must unlock the plot first, great for preventing accidental damage
		$canPlace = $hasRights || $hasAdmin;

		if(!$canPlace) {
			$event->setCancelled(true);
			
			$ownerWithLock = ( ( $plot->owner == $username ) && $plot->locked );
			$helperWithLock = ( ( $plot->isHelper($username) ) && $plot->locked );
			
			if( $ownerWithLock ) {
				$msg = "Your plot is locked - you may unlock it with /islot unlock";
			} elseif( $helperWithLock ) {
				$msg = "This plot is locked by the owner - for owner info use /islot info";
			} else {
				$msg = "This plot does not belong to you and you are not a helper";
			}
			
			@$event->getPlayer()->sendMessage($msg);
		}
		
	}

	public function itemFrame(ItemFrameDropItemEvent $event) {
		/*
		 * Don't change others item frames
		 */

		$this->onEventOnBlock($event);
	}

	public function entityKill(EntityDamageEvent $event){
		/*
		 * Don't remove paintings
		 */
		$levelName = $event->getEntity()->getLevel()->getName();
		if(!$this->plugin->isLevelLoaded($levelName)){
			return;
		}
		if($event->getCause() == EntityDamageEvent::CAUSE_ENTITY_ATTACK){
			if($event instanceof EntityDamageByEntityEvent){
				if(($entity = $event->getEntity()) instanceof Painting){
					
					if(!($player = $event->getDamager()) instanceof Player){
						$event->setCancelled(true);
						return;
					}
				}
				else
					return;
			}
			else
				return;
		}
		else
			return;
		$block = $entity;
		if($block->x == 0 && $block->y == 0 && $block->z == 0){
			// not sure what fires these events but they happen all the
			// time. Lets cancel the event and ignore them!
			$event->setCancelled(true);
			return;
		}
		
		$plot = $this->plugin->getPlotByPosition($block);
		
		if($plot == null){
			// this should never get called because plots have been
			// extended to include the road around in this particular
			// fork of SkyBlock
			$event->setCancelled(true);
			$entity->setHealth($entity->getMaxHealth());
			$event->setDamage(0);
			return;
		}
		
		$username = $player->getName();
		
		$hasRights = (($plot->owner == $username || $plot->isHelper($username)) && !$plot->locked);
		$hasAdmin = (!$plot->locked && $player->hasPermission("skyblock.admin.build.plot"));
		// even admins must unlock the plot first, great for preventing accidental damage
		$canPlace = $hasRights || $hasAdmin;
		
		if(!$canPlace){
			$event->setCancelled(true);
			$entity->setHealth($entity->getMaxHealth());
			$event->setDamage(0);
			
			$ownerWithLock = (($plot->owner == $username) && $plot->locked);
			$helperWithLock = (($plot->isHelper($username)) && $plot->locked);
			
			if($ownerWithLock){
			print "cantplace1";
				$msg = "Your plot is locked - you may unlock it with /islot unlock";
			}
			elseif($helperWithLock){
			print "cantplace2";
				$msg = "This plot is locked by the owner - for owner info use /islot info";
			}
			else{
			print "cantplace3";
				$msg = "This plot does not belong to you and you are not a helper";
			}
			
			@$player->sendMessage($msg);
		}
	}
}