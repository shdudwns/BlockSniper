<?php

declare(strict_types = 1);

namespace BlockHorizons\BlockSniper\brush\registration;

use BlockHorizons\BlockSniper\brush\shapes\CubeShape;
use BlockHorizons\BlockSniper\brush\shapes\CuboidShape;
use BlockHorizons\BlockSniper\brush\shapes\CylinderShape;
use BlockHorizons\BlockSniper\brush\shapes\SphereShape;
use pocketmine\permission\Permission;
use pocketmine\Server;

class ShapeRegistration {

	/** @var string[] */
	private static $shapes = [];
	/** @var string[] */
	private static $shapesIds = [];

	public static function init(): void {
		self::registerShape(SphereShape::class, SphereShape::ID);
		self::registerShape(CubeShape::class, CubeShape::ID);
		self::registerShape(CuboidShape::class, CuboidShape::ID);
		self::registerShape(CylinderShape::class, CylinderShape::ID);
	}

	/**
	 * Registers a new shape with the given Class::class as parameter.
	 * Use $overwrite = true if you'd like to overwrite an existing shape.
	 *
	 * @param string $class
	 * @param int    $id
	 * @param bool   $overwrite
	 *
	 * @return bool
	 */
	public static function registerShape(string $class, int $id, bool $overwrite = false): bool {
		$shortName = str_replace("Shape", "", (new \ReflectionClass($class))->getShortName());
		if(self::shapeExists(strtolower($shortName), $id) && !$overwrite) {
			return false;
		}
		self::$shapesIds[$id] = $shortName;
		self::$shapes[strtolower($shortName)] = $class;
		self::registerPermission(strtolower($shortName));
		return true;
	}

	/**
	 * Returns an array containing the class string of all shapes.
	 *
	 * @return string[]
	 */
	public static function getShapes(): array {
		return self::$shapes;
	}

	/**
	 * Returns an array containing the ID => Name of all shapes.
	 *
	 * @return string[]
	 */
	public static function getShapeIds(): array {
		return self::$shapesIds;
	}

	/**
	 * Returns whether a shape with the given name exists or not.
	 *
	 * @param string $shapeName
	 * @param int    $id
	 *
	 * @return bool
	 */
	public static function shapeExists(string $shapeName, int $id = -1): bool {
		return isset(self::$shapes[$shapeName]) || isset(self::$shapesIds[$id]);
	}

	/**
	 * Returns the class string of the requested shape.
	 *
	 * @param string $shortName
	 *
	 * @return null|string
	 */
	public static function getShape(string $shortName): ?string {
		return self::$shapes[strtolower($shortName)] ?? null;
	}

	/**
	 * Returns a shape class name by ID.
	 *
	 * @param int  $id
	 * @param bool $name
	 *
	 * @return null|string
	 */
	public static function getShapeById(int $id, bool $name = false): ?string {
		if(!isset(self::$shapesIds[$id])) {
			return null;
		}
		return $name ? self::$shapesIds[$id] : self::getShape(strtolower(self::$shapesIds[$id]));
	}

	/**
	 * @param string $shapeName
	 */
	private static function registerPermission(string $shapeName): void {
		$permission = new Permission("blocksniper.shape." . $shapeName, "Allows usage to use the " . $shapeName . " shape.");
		$permission->addParent("blocksniper.shape", Permission::DEFAULT_OP);
		Server::getInstance()->getPluginManager()->addPermission($permission);
	}
}