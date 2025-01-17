<?php

namespace RainLoop\Plugins;

class Property
{
	/**
	 * @var string
	 */
	private $sName;

	/**
	 * @var string
	 */
	private $sLabel;

	/**
	 * @var string
	 */
	private $sDesc;

	/**
	 * @var int
	 */
	private $iType;

	/**
	 * @var bool
	 */
	private $bAllowedInJs;

	/**
	 * @var mixed
	 */
	private $mDefaultValue;

	/**
	 * @var string
	 */
	private $sPlaceholder;

	function __construct(string $sName)
	{
		$this->sName = $sName;
		$this->iType = \RainLoop\Enumerations\PluginPropertyType::STRING;
		$this->mDefaultValue = '';
		$this->sLabel = '';
		$this->sDesc = '';
		$this->bAllowedInJs = false;
		$this->sPlaceholder = '';
	}

	public static function NewInstance(string $sName) : self
	{
		return new self($sName);
	}

	public function SetType(int $iType) : self
	{
		$this->iType = (int) $iType;

		return $this;
	}

	/**
	 * @param mixed $mDefaultValue
	 */
	public function SetDefaultValue($mDefaultValue) : self
	{
		$this->mDefaultValue = $mDefaultValue;

		return $this;
	}

	public function SetPlaceholder(string $sPlaceholder) : self
	{
		$this->sPlaceholder = $sPlaceholder;

		return $this;
	}

	public function SetLabel(string $sLabel) : self
	{
		$this->sLabel = $sLabel;

		return $this;
	}

	public function SetDescription(string $sDesc) : self
	{
		$this->sDesc = $sDesc;

		return $this;
	}

	public function SetAllowedInJs(bool $bValue = true) : self
	{
		$this->bAllowedInJs = $bValue;

		return $this;
	}

	public function Name() : string
	{
		return $this->sName;
	}

	public function AllowedInJs() : bool
	{
		return $this->bAllowedInJs;
	}

	public function Description() : string
	{
		return $this->sDesc;
	}

	public function Label() : string
	{
		return $this->sLabel;
	}

	public function Type() : int
	{
		return $this->iType;
	}

	/**
	 * @return mixed
	 */
	public function DefaultValue()
	{
		return $this->mDefaultValue;
	}

	public function Placeholder() : string
	{
		return $this->sPlaceholder;
	}

	public function ToArray() : array
	{
		return array(
			 '',
			 $this->sName,
			 $this->iType,
			 $this->sLabel,
			 $this->mDefaultValue,
			 $this->sDesc,
			 $this->sPlaceholder
		);
	}
}
