<?php

namespace SkyBlock;

use pocketmine\block\Block;
use pocketmine\level\generator\Generator;
use pocketmine\level\ChunkManager;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use pocketmine\level\generator\biome\Biome;
use pocketmine\level\Level;

class SkyBlockGenerator extends Generator{
	/** @var Level */
	private $level;
	
	/** @var string[] */
	private $settings;
	
	/** @var Block */
	public $roadBlock, $wallBlock, $plotFloorBlock, $plotFillBlock, $bottomBlock;
	
	/** @var int */
	public $roadWidth, $plotSize, $groundHeight;
	private $chunk1, $chunk2;
	const PLOT = 0;
	const ROAD = 1;
	const WALL = 2;
	const ISLAND = 3;

	public function __construct(array $settings = []){
		if(isset($settings["preset"])){
			$settings = json_decode($settings["preset"], true);
			if($settings === false){
				$settings = [];
			}
		}
		else{
			$settings = [];
		}
		$this->roadBlock = $this->parseBlock($settings, "RoadBlock", new Block(5));
		$this->wallBlock = $this->parseBlock($settings, "WallBlock", new Block(44));
		$this->plotFloorBlock = $this->parseBlock($settings, "PlotFloorBlock", new Block(2));
		$this->plotFillBlock = $this->parseBlock($settings, "PlotFillBlock", new Block(3));
		$this->bottomBlock = $this->parseBlock($settings, "BottomBlock", new Block(7));
		$this->roadWidth = $this->parseNumber($settings, "RoadWidth", 7);
		$this->plotSize = $this->parseNumber($settings, "PlotSize", 22);
		$this->groundHeight = $this->parseNumber($settings, "GroundHeight", 32);
		
		$this->settings = [];
		$this->settings["preset"] = json_encode(["RoadBlock" => $this->roadBlock->getId() . (($meta = $this->roadBlock->getDamage())?'':':' . $meta), "WallBlock" => $this->wallBlock->getId() . (($meta = $this->wallBlock->getDamage())?'':':' . $meta), 
				"PlotFloorBlock" => $this->plotFloorBlock->getId() . (($meta = $this->plotFloorBlock->getDamage())?'':':' . $meta), "PlotFillBlock" => $this->plotFillBlock->getId() . (($meta = $this->plotFillBlock->getDamage())?'':':' . $meta), 
				"BottomBlock" => $this->bottomBlock->getId() . (($meta = $this->bottomBlock->getDamage())?'':':' . $meta), "RoadWidth" => $this->roadWidth, "PlotSize" => $this->plotSize, "GroundHeight" => $this->groundHeight]);
	}

	private function parseBlock(&$array, $key, $default){
		if(isset($array[$key])){
			$id = $array[$key];
			if(is_numeric($id)){
				$block = new Block($id);
			}
			else{
				$split = explode(":", $id);
				if(count($split) === 2 and is_numeric($split[0]) and is_numeric($split[1])){
					$block = new Block($split[0], $split[1]);
				}
				else{
					$block = $default;
				}
			}
		}
		else{
			$block = $default;
		}
		return $block;
	}

	private function parseNumber(&$array, $key, $default){
		if(isset($array[$key]) and is_numeric($array[$key])){
			return $array[$key];
		}
		else{
			return $default;
		}
	}

	public function getName(){
		return "skyblock";
	}

	public function getSettings(){
		return $this->settings;
	}

	public function init(ChunkManager $level, Random $random){
		$this->level = $level;
	}

	public function generateChunk($chunkX, $chunkZ){
		$shape = $this->getShape($chunkX << 4, $chunkZ << 4);
		$chunk = $this->level->getChunk($chunkX, $chunkZ);
		$chunk->setGenerated();
		$c = Biome::getBiome(1)->getColor();
		$R = $c >> 16;
		$G = ($c >> 8) & 0xff;
		$B = $c & 0xff;
		
		$bottomBlockId = $this->bottomBlock->getId();
		$bottomBlockMeta = $this->bottomBlock->getDamage();
		$plotFillBlockId = $this->plotFillBlock->getId();
		$plotFillBlockMeta = $this->plotFillBlock->getDamage();
		$plotFloorBlockId = $this->plotFloorBlock->getId();
		$plotFloorBlockMeta = $this->plotFloorBlock->getDamage();
		$roadBlockId = $this->roadBlock->getId();
		$roadBlockMeta = $this->roadBlock->getDamage();
		$wallBlockId = $this->wallBlock->getId();
		$wallBlockMeta = $this->wallBlock->getDamage();
		$groundHeight = $this->groundHeight;
		
		for($Z = 0; $Z < 16; ++$Z){
			for($X = 0; $X < 16; ++$X){
				$chunk->setBiomeId($X, $Z, 1);
				$chunk->setBiomeColor($X, $Z, $R, $G, $B);
				
				// $chunk->setBlock($X, 0, $Z, $bottomBlockId, $bottomBlockMeta);
				// for ($y = 1; $y < $groundHeight; ++$y) {
				// $chunk->setBlock($X, $y, $Z, $plotFillBlockId, $plotFillBlockMeta);
				// }
				$type = $shape[($Z << 4) | $X];
				if($type === self::ISLAND){ // island
					for($isx = 4; $isx < 11; $isx++){
						for($isz = 4; $isz < 11; $isz++){
							$chunk->setBlock($isx, $this->groundHeight + (68 - 64), $isz, Block::GRASS);
						}
					}
					for($isx = 5; $isx < 10; $isx++){
						for($isz = 5; $isz < 10; $isz++){
							$chunk->setBlock($isx, $this->groundHeight + (67 - 64), $isz, Block::DIRT);
							$chunk->setBlock($isx, $this->groundHeight + (72 - 64), $isz, Block::LEAVES); // 72
						}
					}
					for($isx = 6; $isx < 9; $isx++){
						for($isz = 6; $isz < 9; $isz++){
							$chunk->setBlock($isx, $this->groundHeight + (73 - 64), $isz, Block::LEAVES); // 73
							$chunk->setBlock($isx, $this->groundHeight + (66 - 64), $isz, Block::DIRT); // 66
						}
					}
					$chunk->setBlock(7, $this->groundHeight + (64 - 64), 7, Block::BEDROCK); // 0
					$chunk->setBlock(7, $this->groundHeight + (65 - 64), 7, Block::SAND); // 1
					$chunk->setBlock(7, $this->groundHeight + (66 - 64), 7, Block::SAND); // 2
					$chunk->setBlock(7, $this->groundHeight + (67 - 64), 7, Block::SAND); // 3
					$chunk->setBlock(7, $this->groundHeight + (69 - 64), 7, Block::LOG); // 5
					$chunk->setBlock(7, $this->groundHeight + (70 - 64), 7, Block::LOG); // 6
					$chunk->setBlock(7, $this->groundHeight + (71 - 64), 7, Block::LOG); // 7
					$chunk->setBlock(7, $this->groundHeight + (72 - 64), 7, Block::LOG); // 8
					$chunk->setBlock(7, $this->groundHeight + (73 - 64), 7, Block::LOG); // 9
					$chunk->setBlock(4, $this->groundHeight + (68 - 64), 4, Block::AIR); // 68
					$chunk->setBlock(4, $this->groundHeight + (68 - 64), 10, Block::AIR);
					$chunk->setBlock(10, $this->groundHeight + (68 - 64), 4, Block::AIR);
					$chunk->setBlock(10, $this->groundHeight + (68 - 64), 10, Block::AIR);
					$chunk->setBlock(5, $this->groundHeight + (72 - 64), 5, Block::AIR); // 72
					$chunk->setBlock(5, $this->groundHeight + (72 - 64), 9, Block::AIR);
					$chunk->setBlock(9, $this->groundHeight + (72 - 64), 5, Block::AIR);
					$chunk->setBlock(9, $this->groundHeight + (72 - 64), 9, Block::AIR);
					$chunk->setBlock(5, $this->groundHeight + (73 - 64), 7, Block::LEAVES); // 73
					$chunk->setBlock(7, $this->groundHeight + (73 - 64), 5, Block::LEAVES);
					$chunk->setBlock(9, $this->groundHeight + (73 - 64), 7, Block::LEAVES);
					$chunk->setBlock(7, $this->groundHeight + (73 - 64), 9, Block::LEAVES);
					$chunk->setBlock(7, $this->groundHeight + (74 - 64), 6, Block::LEAVES); // 74
					$chunk->setBlock(6, $this->groundHeight + (74 - 64), 7, Block::LEAVES);
					$chunk->setBlock(8, $this->groundHeight + (74 - 64), 7, Block::LEAVES);
					$chunk->setBlock(7, $this->groundHeight + (74 - 64), 8, Block::LEAVES);
					$chunk->setBlock(7, $this->groundHeight + (75 - 64), 7, Block::LEAVES); // 75
					                                                                        // $chunk->setBlock(7, $this->groundHeight + (69 - 64), 8, Block::CHEST);
					$chunk->setBlock(7, $this->groundHeight + (65 - 64), 8, Block::DIRT); // 65
					$chunk->setBlock(8, $this->groundHeight + (65 - 64), 7, Block::DIRT);
					$chunk->setBlock(7, $this->groundHeight + (65 - 64), 6, Block::DIRT);
					$chunk->setBlock(6, $this->groundHeight + (65 - 64), 7, Block::DIRT);
					$chunk->setBlock(5, $this->groundHeight + (66 - 64), 7, Block::DIRT); // 66
					$chunk->setBlock(7, $this->groundHeight + (66 - 64), 5, Block::DIRT);
					$chunk->setBlock(9, $this->groundHeight + (66 - 64), 7, Block::DIRT);
					$chunk->setBlock(7, $this->groundHeight + (66 - 64), 9, Block::DIRT);
					$chunk->setBlock(4, $this->groundHeight + (67 - 64), 7, Block::DIRT); // 67
					$chunk->setBlock(7, $this->groundHeight + (67 - 64), 4, Block::DIRT);
					$chunk->setBlock(7, $this->groundHeight + (67 - 64), 10, Block::DIRT);
					$chunk->setBlock(10, $this->groundHeight + (67 - 64), 7, Block::DIRT);
				}
				elseif($type === self::PLOT){ // PLOT
					                              // $chunk->setBlock($X, $groundHeight, $Z, $plotFloorBlockId, $plotFloorBlockMeta);
				}
				elseif($type === self::ROAD){ // road
					                              // $chunk->setBlock($X, $groundHeight, $Z, $roadBlockId, $roadBlockMeta);
				}
				else{ // border
					$chunk->setBlock($X, $groundHeight, $Z, $roadBlockId, $roadBlockMeta);
					$chunk->setBlock($X, $groundHeight + 1, $Z, $wallBlockId, $wallBlockMeta);
				}
			}
		}
		$chunk->setX($chunkX);
		$chunk->setZ($chunkZ);
		$this->level->setChunk($chunkX, $chunkZ, $chunk);
	}

	/*
	 * public function generateChunk($chunkX, $chunkZ){
	 * $CX = ($chunkX % 5) < 0?(($chunkX % 5) + 5):($chunkX % 5);
	 * $CZ = ($chunkZ % 5) < 0?(($chunkZ % 5) + 5):($chunkZ % 5);
	 * switch($CX . ":" . $CZ){
	 * case '0:0':
	 * {
	 * if($chunk === null){
	 * $chunk = clone $this->level->getChunk($chunkX, $chunkZ);
	 *
	 * $c = Biome::getBiome(1)->getColor();
	 * $R = $c >> 16;
	 * $G = ($c >> 8) & 0xff;
	 * $B = $c & 0xff;
	 * for($x = 0; $x < 16; $x++){
	 * for($z = 0; $z < 16; $z++){
	 * $chunk->setBiomeColor($x, $z, $R, $G, $B);
	 * }
	 * }
	 * for($x = 4; $x < 11; $x++){
	 * for($z = 4; $z < 11; $z++){
	 * $chunk->setBlockId($x, $this->groundHeight + (68 - 64), $z, Block::GRASS);
	 * }
	 * }
	 * for($x = 5; $x < 10; $x++){
	 * for($z = 5; $z < 10; $z++){
	 * $chunk->setBlockId($x, $this->groundHeight + (67 - 64), $z, Block::DIRT);
	 * $chunk->setBlockId($x, $this->groundHeight + (72 - 64), $z, Block::LEAVES); // 72
	 * }
	 * }
	 * for($x = 6; $x < 9; $x++){
	 * for($z = 6; $z < 9; $z++){
	 * $chunk->setBlockId($x, $this->groundHeight + (73 - 64), $z, Block::LEAVES); // 73
	 * $chunk->setBlockId($x, $this->groundHeight + (66 - 64), $z, Block::DIRT); // 66
	 * }
	 * }
	 * $chunk->setBlockId(7, $this->groundHeight + (64 - 64), 7, Block::BEDROCK); // 0
	 * $chunk->setBlockId(7, $this->groundHeight + (65 - 64), 7, Block::SAND); // 1
	 * $chunk->setBlockId(7, $this->groundHeight + (66 - 64), 7, Block::SAND); // 2
	 * $chunk->setBlockId(7, $this->groundHeight + (67 - 64), 7, Block::SAND); // 3
	 * $chunk->setBlockId(7, $this->groundHeight + (69 - 64), 7, Block::LOG); // 5
	 * $chunk->setBlockId(7, $this->groundHeight + (70 - 64), 7, Block::LOG); // 6
	 * $chunk->setBlockId(7, $this->groundHeight + (71 - 64), 7, Block::LOG); // 7
	 * $chunk->setBlockId(7, $this->groundHeight + (72 - 64), 7, Block::LOG); // 8
	 * $chunk->setBlockId(7, $this->groundHeight + (73 - 64), 7, Block::LOG); // 9
	 * $chunk->setBlockId(4, $this->groundHeight + (68 - 64), 4, Block::AIR); // 68
	 * $chunk->setBlockId(4, $this->groundHeight + (68 - 64), 10, Block::AIR);
	 * $chunk->setBlockId(10, $this->groundHeight + (68 - 64), 4, Block::AIR);
	 * $chunk->setBlockId(10, $this->groundHeight + (68 - 64), 10, Block::AIR);
	 * $chunk->setBlockId(5, $this->groundHeight + (72 - 64), 5, Block::AIR); // 72
	 * $chunk->setBlockId(5, $this->groundHeight + (72 - 64), 9, Block::AIR);
	 * $chunk->setBlockId(9, $this->groundHeight + (72 - 64), 5, Block::AIR);
	 * $chunk->setBlockId(9, $this->groundHeight + (72 - 64), 9, Block::AIR);
	 * $chunk->setBlockId(5, $this->groundHeight + (73 - 64), 7, Block::LEAVES); // 73
	 * $chunk->setBlockId(7, $this->groundHeight + (73 - 64), 5, Block::LEAVES);
	 * $chunk->setBlockId(9, $this->groundHeight + (73 - 64), 7, Block::LEAVES);
	 * $chunk->setBlockId(7, $this->groundHeight + (73 - 64), 9, Block::LEAVES);
	 * $chunk->setBlockId(7, $this->groundHeight + (74 - 64), 6, Block::LEAVES); // 74
	 * $chunk->setBlockId(6, $this->groundHeight + (74 - 64), 7, Block::LEAVES);
	 * $chunk->setBlockId(8, $this->groundHeight + (74 - 64), 7, Block::LEAVES);
	 * $chunk->setBlockId(7, $this->groundHeight + (74 - 64), 8, Block::LEAVES);
	 * $chunk->setBlockId(7, $this->groundHeight + (75 - 64), 7, Block::LEAVES); // 75
	 * // $chunk->setBlockId(7, $this->groundHeight + (69 - 64), 8, Block::CHEST);
	 * $chunk->setBlockId(7, $this->groundHeight + (65 - 64), 8, Block::DIRT); // 65
	 * $chunk->setBlockId(8, $this->groundHeight + (65 - 64), 7, Block::DIRT);
	 * $chunk->setBlockId(7, $this->groundHeight + (65 - 64), 6, Block::DIRT);
	 * $chunk->setBlockId(6, $this->groundHeight + (65 - 64), 7, Block::DIRT);
	 * $chunk->setBlockId(5, $this->groundHeight + (66 - 64), 7, Block::DIRT); // 66
	 * $chunk->setBlockId(7, $this->groundHeight + (66 - 64), 5, Block::DIRT);
	 * $chunk->setBlockId(9, $this->groundHeight + (66 - 64), 7, Block::DIRT);
	 * $chunk->setBlockId(7, $this->groundHeight + (66 - 64), 9, Block::DIRT);
	 * $chunk->setBlockId(4, $this->groundHeight + (67 - 64), 7, Block::DIRT); // 67
	 * $chunk->setBlockId(7, $this->groundHeight + (67 - 64), 4, Block::DIRT);
	 * $chunk->setBlockId(7, $this->groundHeight + (67 - 64), 10, Block::DIRT);
	 * $chunk->setBlockId(10, $this->groundHeight + (67 - 64), 7, Block::DIRT);
	 * }
	 * $chunk = clone $this->chunk1;
	 * $chunk->setX($chunkX);
	 * $chunk->setZ($chunkZ);
	 * $this->level->setChunk($chunkX, $chunkZ, $chunk);
	 * break;
	 * }
	 *
	 * default:
	 * {
	 * if($this->chunk2 === null){
	 * $this->chunk2 = clone $this->level->getChunk($chunkX, $chunkZ);
	 *
	 * $c = Biome::getBiome(1)->getColor();
	 * $R = $c >> 16;
	 * $G = ($c >> 8) & 0xff;
	 * $B = $c & 0xff;
	 * for($x = 0; $x < 16; $x++){
	 * for($z = 0; $z < 16; $z++){
	 * $this->chunk2->setBiomeColor($x, $z, $R, $G, $B);
	 * }
	 * }
	 * $chunk = clone $this->chunk2;
	 * $chunk->setX($chunkX);
	 * $chunk->setZ($chunkZ);
	 * $this->level->setChunk($chunkX, $chunkZ, $chunk);
	 * break;
	 * }
	 * }
	 * }
	 * }
	 */
	public function getShape($x, $z){
		$totalSize = $this->plotSize + $this->roadWidth;
		
		if($x >= 0){
			$X = $x % $totalSize;
		}
		else{
			$X = $totalSize - abs($x % $totalSize);
		}
		if($z >= 0){
			$Z = $z % $totalSize;
		}
		else{
			$Z = $totalSize - abs($z % $totalSize);
		}
		
		$startX = $X;
		$shape = new \SplFixedArray(256);
		
		for($z = 0; $z < 16; $z++, $Z++){
			if($Z === $totalSize){
				$Z = 0;
			}
			if($Z == floor($this->plotSize / 2)){
				$typeZ = self::ISLAND;
			}
			elseif($Z < $this->plotSize){
				$typeZ = self::PLOT;
			}
			elseif($Z === $this->plotSize or $Z === ($totalSize - 1)){
				$typeZ = self::WALL;
			}
			else{
				$typeZ = self::ROAD;
			}
			
			for($x = 0, $X = $startX; $x < 16; $x++, $X++){
				if($X === $totalSize){
					$X = 0;
				}
				if($X == floor($this->plotSize / 2)){
					$typeX = self::ISLAND;
				}
				elseif($X < $this->plotSize){
					$typeX = self::PLOT;
				}
				elseif($X === $this->plotSize or $X === ($totalSize - 1)){
					$typeX = self::WALL;
				}
				else{
					$typeX = self::ROAD;
				}
				if($typeX === $typeZ){
					$type = $typeX;
				}
				elseif($typeX === self::ISLAND || $typeZ === self::ISLAND){
					$type = self::PLOT;
				}
				elseif($typeX === self::PLOT){
					$type = $typeZ;
				}
				elseif($typeZ === self::PLOT){
					$type = $typeX;
				}
				else{
					$type = self::ROAD;
				}
				$shape[($z << 4) | $x] = $type;
			}
		}
		return $shape;
	}

	public function populateChunk($chunkX, $chunkZ){}

	public function getSpawn(){
		return new Vector3(0, $this->groundHeight, 0);
	}
}