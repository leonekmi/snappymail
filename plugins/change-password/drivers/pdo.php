<?php

class ChangePasswordDriverPDO
{
	const
		NAME        = 'PDO',
		DESCRIPTION = 'Use your own SQL (PDO) statement (with wildcards).';

	/**
	 * @var \RainLoop\Config\Plugin
	 */
	private $oConfig = null;

	/**
	 * @var \MailSo\Log\Logger
	 */
	private $oLogger = null;

	function __construct(\RainLoop\Config\Plugin $oConfig, \MailSo\Log\Logger $oLogger)
	{
		$this->oConfig = $oConfig;
		$this->oLogger = $oLogger;
	}

	public static function isSupported() : bool
	{
		return \class_exists('PDO', false);
	}

	public static function configMapping() : array
	{
		return array(
			\RainLoop\Plugins\Property::NewInstance('pdo_dsn')->SetLabel('DSN')
				->SetDefaultValue('mysql:host=localhost;dbname=snappymail;charset=utf8'),
			\RainLoop\Plugins\Property::NewInstance('pdo_user')->SetLabel('User'),
			\RainLoop\Plugins\Property::NewInstance('pdo_password')->SetLabel('Password')
				->SetType(\RainLoop\Enumerations\PluginPropertyType::PASSWORD),
			\RainLoop\Plugins\Property::NewInstance('pdo_sql')->SetLabel('Statement')
				->SetType(\RainLoop\Enumerations\PluginPropertyType::STRING_TEXT)
				->SetDescription('SQL statement (allowed wildcards :email, :oldpass, :newpass, :domain, :username).')
				->SetDefaultValue('UPDATE table SET password = :newpass WHERE domain = :domain AND username = :username and oldpass = :oldpass'),
			\RainLoop\Plugins\Property::NewInstance('pdo_encrypt')->SetLabel('Encryption')
				->SetType(\RainLoop\Enumerations\PluginPropertyType::SELECTION)
				->SetDefaultValue(array('none', 'bcrypt', 'Argon2i', 'Argon2id', 'SHA256-CRYPT', 'SHA512-CRYPT'))
				->SetDescription('In what way do you want the passwords to be encrypted?'),
			\RainLoop\Plugins\Property::NewInstance('pdo_encryptprefix')->SetLabel('Encrypt prefix')
				->SetDescription('Optional encrypted password prefix, like {ARGON2I} or {BLF-CRYPT} or {SHA512-CRYPT}')
		);
	}

	public function ChangePassword(\RainLoop\Model\Account $oAccount, string $sPrevPassword, string $sNewPassword) : bool
	{
		try
		{
			$conn = new \PDO(
				$this->oConfig->Get('plugin', 'pdo_dsn', ''),
				$this->oConfig->Get('plugin', 'pdo_user', ''),
				$this->oConfig->Get('plugin', 'pdo_password', ''),
				array(
					\PDO::ATTR_EMULATE_PREPARES  => true,
					\PDO::ATTR_PERSISTENT        => true,
					\PDO::ATTR_ERRMODE           => \PDO::ERRMODE_EXCEPTION
				)
			);

			$sEmail = $oAccount->Email();
			$encrypt = $this->oConfig->Get('plugin', 'pdo_encrypt', '');
			$encrypt_prefix = $this->oConfig->Get('plugin', 'pdo_encryptprefix', '');

			$placeholders = array(
				':email' => $sEmail,
				':oldpass' => $encrypt_prefix . \ChangePasswordPlugin::encrypt($encrypt, $sPrevPassword),
				':newpass' => $encrypt_prefix . \ChangePasswordPlugin::encrypt($encrypt, $sNewPassword),
				':domain' => \MailSo\Base\Utils::GetDomainFromEmail($sEmail),
				':username' => \MailSo\Base\Utils::GetAccountNameFromEmail($sEmail)
			);

			$sql = $this->oConfig->Get('plugin', 'pdo_sql', '');

			$statement = $conn->prepare($sql);

			// we have to check that all placehoders are used in the query, passing any unused placeholders will generate an error
			foreach ($placeholders as $placeholder => $value) {
				if (\preg_match_all("/{$placeholder}(?![a-zA-Z0-9\-])/", $sql)) {
					$statement->bindValue($placeholder, $value);
				}
			}

			// and execute
			return !!$statement->execute();
		}
		catch (\Exception $oException)
		{
			if ($this->oLogger) {
				$this->oLogger->WriteException($oException);
			}
		}
		return false;
	}
}
