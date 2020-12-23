<?php declare(strict_types = 1);

/**
 * StateFactory.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:RedisDbStoragePlugin!
 * @subpackage     States
 * @since          0.1.0
 *
 * @date           03.03.20
 */

namespace FastyBird\RedisDbStoragePlugin\States;

use FastyBird\RedisDbStoragePlugin\Exceptions;
use Nette\Utils;
use phpDocumentor;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionType;
use Reflector;
use stdClass;
use Throwable;

/**
 * State object factory
 *
 * @package        FastyBird:RedisDbStoragePlugin!
 * @subpackage     States
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class StateFactory
{

	/**
	 * @param string $stateClass
	 * @param string $raw
	 *
	 * @return IState
	 */
	public static function create(
		string $stateClass,
		string $raw
	): IState {
		if (!class_exists($stateClass)) {
			throw new Exceptions\InvalidStateException('State could not be created - hyr');
		}

		try {
			$decoded = Utils\Json::decode($raw);

		} catch (Utils\JsonException $ex) {
			throw new Exceptions\InvalidArgumentException('Provided state content is not valid JSON.');
		}

		try {
			$rc = new ReflectionClass($stateClass);

			$constructor = $rc->getConstructor();

			if ($constructor !== null) {
				$state = $rc->newInstanceArgs(self::autowireArguments($constructor, $decoded));

			} else {
				$state = new $stateClass();
			}

		} catch (Throwable $ex) {
			throw new Exceptions\InvalidStateException('State could not be created - hyr 2', 0, $ex);
		}

		$properties = self::getProperties($rc);

		foreach ($properties as $rp) {
			$varAnnotation = self::parseAnnotation($rp, 'var');

			if (
				array_search($rp->getName(), array_keys(get_object_vars($decoded)), true) !== false
				&& property_exists($decoded, $rp->getName())
			) {
				/** @phpstan-ignore-next-line */
				$value = $decoded->{$rp->getName()};

				$methodName = 'set' . ucfirst($rp->getName());

				if ($varAnnotation === 'int') {
					$value = (int) $value;

				} elseif ($varAnnotation === 'float') {
					$value = (float) $value;

				} elseif ($varAnnotation === 'bool') {
					$value = (bool) $value;

				} elseif ($varAnnotation === 'string') {
					$value = (string) $value;
				}

				try {
					$rm = new ReflectionMethod($stateClass, $methodName);

					if ($rm->isPublic()) {
						$callback = [$state, $methodName];

						// Try to call state setter
						if (is_callable($callback)) {
							call_user_func_array($callback, [$value]);
						}
					}

				} catch (ReflectionException $ex) {
					continue;

				} catch (Throwable $ex) {
					throw new Exceptions\InvalidStateException('State could not be created - hyr 3', 0, $ex);
				}
			}
		}

		return $state;
	}

	/**
	 * This method was inspired with same method in Nette framework
	 *
	 * @param ReflectionMethod $method
	 * @param stdClass $decoded
	 *
	 * @return mixed[]
	 *
	 * @throws ReflectionException
	 */
	private static function autowireArguments(
		ReflectionMethod $method,
		stdClass $decoded
	): array {
		$res = [];

		foreach ($method->getParameters() as $num => $parameter) {
			$parameterName = $parameter->getName();
			$parameterType = self::getParameterType($parameter);

			if (
				!$parameter->isVariadic()
				&& array_search($parameterName, array_keys(get_object_vars($decoded)), true) !== false
			) {
				$res[$num] = $decoded->{$parameterName};

			} elseif ($parameterName === 'id' && property_exists($decoded, 'id')) {
				$res[$num] = $decoded->id;

			} elseif ($parameterName === 'raw') {
				$res[$num] = Utils\Json::encode($decoded);

			} elseif (
				(
					$parameterType !== null
					&& $parameter->allowsNull()
				)
				|| $parameter->isOptional()
				|| $parameter->isDefaultValueAvailable()
			) {
				// !optional + defaultAvailable = func($a = NULL, $b) since 5.4.7
				// optional + !defaultAvailable = i.e. Exception::__construct, mysqli::mysqli, ...
				$res[$num] = $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null;
			}
		}

		return $res;
	}

	/**
	 * @param ReflectionParameter $param
	 *
	 * @return string|NULL
	 */
	private static function getParameterType(ReflectionParameter $param): ?string
	{
		if ($param->hasType()) {
			$rt = $param->getType();

			if ($rt instanceof ReflectionType && method_exists($rt, 'getName')) {
				$type = $rt->getName();

				return strtolower($type) === 'self' && $param->getDeclaringClass() !== null ? $param->getDeclaringClass()
					->getName() : $type;
			}
		}

		return null;
	}

	/**
	 * @param Reflector $rc
	 *
	 * @return ReflectionProperty[]
	 */
	private static function getProperties(Reflector $rc): array
	{
		if (!$rc instanceof ReflectionClass) {
			return [];
		}

		$properties = [];

		foreach ($rc->getProperties() as $rcProperty) {
			$properties[] = $rcProperty;
		}

		if ($rc->getParentClass() !== false) {
			$properties = array_merge($properties, self::getProperties($rc->getParentClass()));
		}

		return $properties;
	}

	/**
	 * @param ReflectionProperty $rp
	 * @param string $name
	 *
	 * @return string|NULL
	 */
	private static function parseAnnotation(ReflectionProperty $rp, string $name): ?string
	{
		if ($rp->getDocComment() === false) {
			return null;
		}

		$factory = phpDocumentor\Reflection\DocBlockFactory::createInstance();
		$docblock = $factory->create($rp->getDocComment());

		foreach ($docblock->getTags() as $tag) {
			if ($tag->getName() === $name) {
				return trim((string) $tag);
			}
		}

		return null;
	}

}
