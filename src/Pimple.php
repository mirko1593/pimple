<?php 

namespace Acme;

class Pimple implements \ArrayAccess
{
	protected $values = array();

	/**
	 * Instantiate the container
	 *
	 * Objects (closure) and parameters can be passed as argument to container.
	 * 	
	 * @param array $values Objects or Parameters.
	 */
	public function __construct(array $values = [])
	{
		$this->values = $values;
	}

	/**
	 * Sets a object or a parameter.
	 *
	 * Objects must be defined as closure.
	 * 
	 * @param  string $id    The unique identifier for the parameter or object.
	 * @param  mixed $value  The value of parameter or closure to defined an object.
	 */
	public function offsetSet($id, $value) 
	{
		$this->values[$id] = $value;
	}

	/**
	 * Gets an object or a parameter
	 * 
	 * @param  string $id The unique identifier for the parameter or object.
	 * 
	 * @return mixed      The value of parameter or object.
	 *
	 * @throws InvalidArgumentException 	If the identifier is not defined.
	 */
	public function offsetGet($id) 
	{
		if ( ! array_key_exists($id, $this->values)) {
			throw new InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $id));
		}

		$isFactory = is_object($this->values[$id]) && method_exists($this->values[$id], '__invoke');

		return $isFactory ? $this->values[$id]($this) : $this->values[$id];
	}

	/**
	 * Check if a parameter or an object exists.
	 * 	
	 * @param  string $id The unique identifier for the parameter or object.	
	 *                   
	 * @return Boolean     
	 */
	public function offsetExists($id) 
	{
		return array_key_exists($id, $this->values);
	}

	/**
	 * Unset a parameter or an object.
	 * 
	 * @param  string $id The unique identifier for the parameter or object.
	 */
	public function offsetUnset($id)
	{
		unset($this->values[$id]);
	}

	/**
	 * Return a closure that stores the result of the given service definition
	 * for uniqueness in the scope of this instance of Pimple.
	 * 
	 * @param  callable $callable A service definition to wrap for uniqueness
	 * 
	 * @return Closure           The wrapped closure.
	 */
	public function share($callable)
	{
		if ( ! is_object($callable) || ! method_exists($callable, '__invoke')) {
			throw new InvalidArgumentException('Service definition is not a Closure or a invokablel object.');
		}

		return function ($c) use ($callable) {
			static $object;

			if ($object == null) {
				$object = $callable(c);
			}

			return $object;
		};
	}

	/**
	 * Protect a callable from being interpreted as a service.
	 *
	 * This is usefull when you want to store a callable as a parameter.
	 * 
	 * @param  callable $callable A callable to protect from being evaluated.
	 * 
	 * @return Closure           The protected closure.
	 */
	public static function protect($callable)
	{
		if ( ! is_object($callable) || ! method_exists($callable, '__invoke')) {
			throw new InvalidArgumentException('Callable is not a Closure or an invokable object.');		
		}

		return function ($c) use ($callable) {
			return $callable;
		};
	}

	/**
	 * Gets a parameter or the closure defining an object.
	 * 
	 * @param  string $id The unique identifier for parameter or object.
	 * 
	 * @return miexed     The value of the parameter or the closure defing an object.
	 * 
	 * @throws InvalidArgumentException If the identifier is not defined.
	 */
	public function raw($id)
	{	
		if ( ! array_key_exists($id, $this->values)) {
			throw new InvalidArgumentException(sprintf('Identifier "%s" is not defined', $id));
		}

		return $this->values[$id];
	}

	/**
	 * Extends an object definition
	 *
	 * Useful when you want to extend an existing object definition, without
	 * necessarily loading that object.
	 * 
	 * @param  string $id       The unique identifier for parameter or object.
	 * @param  callable $callable A service defintion to extend the original.
	 * 
	 * @return closure           The wrapped closure.
	 * 
	 * @throws InvalidArgumentException If the identifier is not defined or not a service definition.
	 */
	public function extend($id, $callable)
	{
		if ( ! array_key_exists($id, $this->values)) {
			throw new InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $id));
		}

		if ( ! is_object($this->values[$id]) || ! method_exists($this->values[$id], '__invoke')) {
			throw new InvalidArgumentException(sprintf('Identifier "%s" does not contain an object definition.', $id));
		}

		if ( ! is_object($callable) || ! method_exists($callable, '__invoke')) {
			throw new InvalidArgumentException('Extension service definition is not a closure or invokable object');
		}

		$factory = $this->values[$id];

		return $this->values[$id] = function ($c) use ($callable, $factory) {
			return $callable($factory($c), $c);
		};
	}

	/**
	 * Return all defined value names.
	 * 
	 * @return array An array of value names.
	 */
	public function keys() 
	{
		return array_keys($this->values);
	}
}