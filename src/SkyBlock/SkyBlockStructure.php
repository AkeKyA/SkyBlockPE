<?php
use pocketmine\math\Vector3;
use pocketmine\level\ChunkManager;

abstract class SkyBlockStructure{

	public static function placeObject(ChunkManager $level, Vector3 $vec){
		for($x = 4; $x < 11; $x++){
			for($z = 4; $z < 11; $z++){
				$level->setBlockIdAt($x, 68, $z, Block::GRASS);
			}
		}
		for($x = 5; $x < 10; $x++){
			for($z = 5; $z < 10; $z++){
				$level->setBlockIdAt($x, 67, $z, Block::DIRT);
				$level->setBlockIdAt($x, 72, $z, Block::LEAVES); // 72
			}
		}
		for($x = 6; $x < 9; $x++){
			for($z = 6; $z < 9; $z++){
				$level->setBlockIdAt($x, 73, $z, Block::LEAVES); // 73
				$level->setBlockIdAt($x, 66, $z, Block::DIRT); // 66
			}
		}
		$level->setBlockIdAt(7, 64, 7, Block::BEDROCK); // 0
		$level->setBlockIdAt(7, 65, 7, Block::SAND); // 1
		$level->setBlockIdAt(7, 66, 7, Block::SAND); // 2
		$level->setBlockIdAt(7, 67, 7, Block::SAND); // 3
		$level->setBlockIdAt(7, 69, 7, Block::LOG); // 5
		$level->setBlockIdAt(7, 70, 7, Block::LOG); // 6
		$level->setBlockIdAt(7, 71, 7, Block::LOG); // 7
		$level->setBlockIdAt(7, 72, 7, Block::LOG); // 8
		$level->setBlockIdAt(7, 73, 7, Block::LOG); // 9
		$level->setBlockIdAt(4, 68, 4, Block::AIR); // 68
		$level->setBlockIdAt(4, 68, 10, Block::AIR);
		$level->setBlockIdAt(10, 68, 4, Block::AIR);
		$level->setBlockIdAt(10, 68, 10, Block::AIR);
		$level->setBlockIdAt(5, 72, 5, Block::AIR); // 72
		$level->setBlockIdAt(5, 72, 9, Block::AIR);
		$level->setBlockIdAt(9, 72, 5, Block::AIR);
		$level->setBlockIdAt(9, 72, 9, Block::AIR);
		$level->setBlockIdAt(5, 73, 7, Block::LEAVES); // 73
		$level->setBlockIdAt(7, 73, 5, Block::LEAVES);
		$level->setBlockIdAt(9, 73, 7, Block::LEAVES);
		$level->setBlockIdAt(7, 73, 9, Block::LEAVES);
		$level->setBlockIdAt(7, 74, 6, Block::LEAVES); // 74
		$level->setBlockIdAt(6, 74, 7, Block::LEAVES);
		$level->setBlockIdAt(8, 74, 7, Block::LEAVES);
		$level->setBlockIdAt(7, 74, 8, Block::LEAVES);
		$level->setBlockIdAt(7, 75, 7, Block::LEAVES); // 75
		                                              // $level->setBlockIdAt(7,69, 8, Block::CHEST);
		$level->setBlockIdAt(7, 65, 8, Block::DIRT); // 65
		$level->setBlockIdAt(8, 65, 7, Block::DIRT);
		$level->setBlockIdAt(7, 65, 6, Block::DIRT);
		$level->setBlockIdAt(6, 65, 7, Block::DIRT);
		$level->setBlockIdAt(5, 66, 7, Block::DIRT); // 66
		$level->setBlockIdAt(7, 66, 5, Block::DIRT);
		$level->setBlockIdAt(9, 66, 7, Block::DIRT);
		$level->setBlockIdAt(7, 66, 9, Block::DIRT);
		$level->setBlockIdAt(4, 67, 7, Block::DIRT); // 67
		$level->setBlockIdAt(7, 67, 4, Block::DIRT);
		$level->setBlockIdAt(7, 67, 10, Block::DIRT);
		$level->setBlockIdAt(10, 67, 7, Block::DIRT);
	}
}