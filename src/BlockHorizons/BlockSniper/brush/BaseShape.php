<?php

declare(strict_types = 1);

namespace BlockHorizons\BlockSniper\brush;

use BlockHorizons\BlockSniper\brush\async\tasks\BrushTask;
use BlockHorizons\BlockSniper\brush\registration\ShapeRegistration;
use pocketmine\block\Block;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\Server;

abstract class BaseShape {

	const ID = -1;

	const SHAPE_SPHERE = 0;
	const SHAPE_CUBE = 1;
	const SHAPE_CUBOID = 2;
	const SHAPE_CYLINDER = 3;

	/** @var int */
	protected $level = 0;
	/** @var array */
	protected $center = [];
	/** @var bool */
	protected $hollow = false;
	/** @var int */
	protected $height = 0;
	/** @var string */
	protected $playerName = "";

	public function __construct(Player $player, Level $level, Position $center, bool $hollow) {
		$this->playerName = $player->getName();
		$this->level = $level->getId();
		$this->center = [$center->x, $center->y, $center->z, $center->level->getId()];
		$this->hollow = $hollow;
	}

	/**
	 * Returns all blocks in the shape if $partially is false. If true, only returns part of the shape, specified by $blocksPerTick.
	 *
	 * @param $vectorOnly
	 *
	 * @return Block[]|Vector3[]
	 */
	public abstract function getBlocksInside(bool $vectorOnly = false): array;

	/**
	 * Returns the approximate amount of processed blocks in the shape. This may not be perfectly accurate.
	 *
	 * @return int
	 */
	public abstract function getApproximateProcessedBlocks(): int;

	/**
	 * @param Server $server
	 *
	 * @return Player|null
	 */
	public function getPlayer(Server $server): ?Player {
		return $server->getPlayer($this->playerName);
	}

	/**
	 * @return int
	 */
	public function getLevelId(): int {
		return $this->level;
	}

	/**
	 * Returns the center of the shape made, or the target block.
	 *
	 * @return Position
	 */
	public function getCenter(): Position {
		return new Position($this->center[0], $this->center[1], $this->center[2], Server::getInstance()->getLevel($this->center[3]));
	}

	/**
	 * Returns true if the shape is hollow, false if it is not.
	 *
	 * @return bool
	 */
	public function isHollow(): bool {
		return $this->hollow;
	}

	/**
	 * Returns the permission required to use the shape.
	 *
	 * @return string
	 */
	public function getPermission(): string {
		return "blocksniper.shape." . strtolower(ShapeRegistration::getShapeById(self::ID, true));
	}

	/**
	 * Returns the name of the shape.
	 *
	 * @return string
	 */
	public abstract function getName(): string;

	/**
	 * @param BaseType $type
	 *
	 * @return bool
	 */
	public function editAsynchronously(BaseType $type): bool {
		$this->getLevel()->getServer()->getScheduler()->scheduleAsyncTask(new BrushTask($this, $type, $this->getTouchedChunks()));
		return true;
	}

	/**
	 * Returns the level the shape is made in.
	 *
	 * @return Level
	 */
	public function getLevel(): Level {
		return Server::getInstance()->getLevel($this->level);
	}

	/**
	 * @return array
	 */
	public abstract function getTouchedChunks(): array;
}
