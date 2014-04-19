<?php

/**
 * Licensed under the MIT license.
 *
 * For the full copyright and license information, please view the LICENSE file.
 *
 * @author Rémi Lanvin <remi@cloudconnected.fr>
 * @link https://github.com/rlanvin/php-ip 
 */

/**
 * Class to manipulate IPv4
 *
 * The address is stored as a **SIGNED** 32bit integer (because PHP doesn't support unsigned type).
 */
class IPv4 extends IP
{
	const IP_VERSION = 4;
	const MAX_INT = 0xFFFFFFFF;
	const NB_BITS = 32;

	public function getVersion()
	{
		return self::IP_VERSION;
	}

	/**
	 * Constructor tries to guess what is the $ip
	 *
	 * @param $ip mixed String, binary string, int or float
	 */
	public function __construct($ip)
	{
		if ( is_int($ip) ) {
			$this->ip = $ip;
		}
		elseif ( is_float($ip) && floor($ip) == $ip ) {
			$this->ip = intval($ip);
		}
		elseif ( is_string($ip) ) {
			if ( ! ctype_print($ip) ) {
				if ( strlen($ip) != 4 ) {
					throw new InvalidArgumentException("The binary string is not a valid IPv4 address");
				}
				$this->ip = @ inet_ntop($ip);
				if ( $this->ip === false ) {
					throw new InvalidArgumentException("The binary string is not a valid IPv4 address");
				}
				$this->ip = ip2long($this->ip);
			}
			elseif ( filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ) {
				$this->ip = ip2long($ip);
			}
			elseif ( ctype_digit($ip) ) {
				if ( $ip > 0xFFFFFFFF ) {
					throw new InvalidArgumentException("$ip is not a valid decimal IPv4 address");
				}
				// convert "unsigned long" (string) to signed int (int)
				$this->ip = intval(doubleval($ip));
			}
			else {
				throw new InvalidArgumentException("$ip is not a valid IPv4 address");
			}
		}
		else {
			throw new InvalidArgumentException("Unsupported argument type: ".gettype($ip));
		}
	}

	/**
	 * Returns numeric representation of the IP
	 *
	 * @param $base int
	 * @return string
	 */
	public function numeric($base = 10)
	{
		if ( $base < 2 || $base > 36 ) {
			throw new InvalidArgumentException("Base must be between 2 and 36 (included)");
		}
		return base_convert(sprintf('%u',$this->ip),10,$base);
	}

	/**
	 * Returns human readable representation of the IP
	 *
	 * @return string
	 */
	public function humanReadable()
	{
		return long2ip($this->ip);
	}

	/**
	 * Bitwise AND
	 *
	 * @param $value mixed anything that can be converted into an IP object
	 * @return IP
	 */
	public function bit_and($value)
	{
		if ( ! $value instanceof self ) {
			$value = new self($value);
		}

		return new self($this->ip & $value->ip);
	}

	/**
	 * Bitwise OR
	 *
	 * @param $value mixed anything that can be converted into an IP object
	 * @return IP
	 */
	public function bit_or($value)
	{
		if ( ! $value instanceof self ) {
			$value = new self($value);
		}

		return new self($this->ip | $value->ip);
	}

	/**
	 * Plus (+)
	 *
	 * @throws OutOfBoundsException
	 * @param $value mixed anything that can be converted into an IP object
	 * @return IP
	 */
	public function plus($value)
	{
		if ( $value < 0 ) {
			return $this->minus(-1*$value);
		}

		if ( $value == 0 ) {
			return clone $self;
		}

		if ( ! $value instanceof self ) {
			$value = new self($value);
		}

		// test boundaries
		$result = $this->numeric() + $value->numeric();

		if ( $result < 0 || $result > self::MAX_INT ) {
			throw new OutOfBoundsException();
		}

		return new self($result);
	}

	/**
	 * Minus(-)
	 *
	 * @throws OutOfBoundsException
	 * @param $value mixed anything that can be converted into an IP object
	 * @return IP
	 */
	public function minus($value)
	{
		if ( $value < 0 ) {
			return $this->plus(-1*$value);
		}

		if ( $value == 0 ) {
			return clone $self;
		}

		if ( ! $value instanceof self ) {
			$value = new self($value);
		}

		// test boundaries
		$result = $this->numeric() - $value->numeric();

		if ( $result < 0 || $result > self::MAX_INT ) {
			throw new OutOfBoundsException();
		}

		return new self($result);
	}

	/**
	 * Return true if the address is reserved per iana-ipv4-special-registry
	 */
	public function isPrivate()
	{
		if ( $this->is_private === null ) {
			$this->is_private =
				$this->isIn('0.0.0.0/8') ||
				$this->isIn('10.0.0.0/8') ||
				$this->isIn('127.0.0.0/8') ||
				$this->isIn('169.254.0.0/16') ||
				$this->isIn('172.16.0.0/12') ||
				$this->isIn('192.0.0.0/29') ||
				$this->isIn('192.0.0.170/31') ||
				$this->isIn('192.0.2.0/24') ||
				$this->isIn('192.168.0.0/16') ||
				$this->isIn('198.18.0.0/15') ||
				$this->isIn('198.51.100.0/24') ||
				$this->isIn('203.0.113.0/24') ||
				$this->isIn('240.0.0.0/4') ||
				$this->isIn('255.255.255.255/32');
		}
		return $this->is_private;
	}
}