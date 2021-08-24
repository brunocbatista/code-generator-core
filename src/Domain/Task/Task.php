<?php
declare(strict_types=1);

namespace App\Domain\Task;

use JsonSerializable;

class Task implements JsonSerializable {
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
	 * @var string
	 */
	private string $title;

	/**
	 * @var string
	 */
	private string $description;

	/**
	 * @var string
	 */
	private string $color;

	/**
	 * @var string
	 */
	private string $deadline;

	/**
	 * @var string
	 */
	private string $completed_at;

	/**
	 * @param string $name
	 * @param string $email
	 * @param string $password
	 * @param string $title
	 * @param string $description
	 * @param string $color
	 * @param string $deadline
	 * @param string $completed_at
	 */
	public function __construct(string $name, string $email, string $password, string $title, string $description, string $color, string $deadline, string $completed_at)
	{
		$this->name = $name;
		$this->email = $email;
		$this->password = $password;
		$this->title = $title;
		$this->description = $description;
		$this->color = $color;
		$this->deadline = $deadline;
		$this->completed_at = $completed_at;
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
	 * @return string
	 */
	public function getTitle(): string
	{
		return $this->title;
	}

	/**
	 * @param string $title
	 * @void
	 */
	public function setTitle(string $title): void
	{
		$this->title = $title;
	}

	/**
	 * @return string
	 */
	public function getDescription(): string
	{
		return $this->description;
	}

	/**
	 * @param string $description
	 * @void
	 */
	public function setDescription(string $description): void
	{
		$this->description = $description;
	}

	/**
	 * @return string
	 */
	public function getColor(): string
	{
		return $this->color;
	}

	/**
	 * @param string $color
	 * @void
	 */
	public function setColor(string $color): void
	{
		$this->color = $color;
	}

	/**
	 * @return string
	 */
	public function getDeadline(): string
	{
		return $this->deadline;
	}

	/**
	 * @param string $deadline
	 * @void
	 */
	public function setDeadline(string $deadline): void
	{
		$this->deadline = $deadline;
	}

	/**
	 * @return string
	 */
	public function getCompletedAt(): string
	{
		return $this->completed_at;
	}

	/**
	 * @param string $completed_at
	 * @void
	 */
	public function setCompletedAt(string $completed_at): void
	{
		$this->completed_at = $completed_at;
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
			'title' => $this->title,
			'description' => $this->description,
			'color' => $this->color,
			'deadline' => $this->deadline,
			'completed_at' => $this->completed_at,
		];
	}
}