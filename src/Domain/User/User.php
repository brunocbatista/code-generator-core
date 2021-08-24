<?php
declare(strict_types=1);

namespace App\Domain\User;

use JsonSerializable;

class User implements JsonSerializable {
	/**
	 * @var string
	 */
	private string $name;

	/**
	 * @var string
	 */
	private string $email;

	/**
	 * @var string
	 */
	private string $password;

	/**
	 * @param string $name
	 * @param string $email
	 * @param string $password
	 */
	public function __construct(string $name, string $email, string $password)
	{
		$this->name = $name;
		$this->email = $email;
		$this->password = $password;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 * @void
	 */
	public function setName(string $name): void
	{
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getEmail(): string
	{
		return $this->email;
	}

	/**
	 * @param string $email
	 * @void
	 */
	public function setEmail(string $email): void
	{
		$this->email = $email;
	}

	/**
	 * @return string
	 */
	public function getPassword(): string
	{
		return $this->password;
	}

	/**
	 * @param string $password
	 * @void
	 */
	public function setPassword(string $password): void
	{
		$this->password = $password;
	}

	/**
	 * @return array
	 */
	public function jsonSerialize()
	{
		return [
			'name' => $this->name,
			'email' => $this->email,
			'password' => $this->password,
		];
	}
}