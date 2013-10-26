<?php

/**
 * Class Auth
 * @author greyblue9
 *
 * Provides authentication data to pull down groups/dials data from Speed Dial 2 sync servers.
 *
 * NOTE: -- IMPORTANT! --
 * In order for the web app to work, you must update the $username and $passwordHashed
 * variables in this class to match those of your Speed Dial 2 user account.
 *
 */

class Auth {

	// Fill in values for the following two static class variables:

	/** @var string SpeedDial2 web Username
	 * (as entered on https://speeddial2.com/login) */
	private static $username = '';

	/** @var string SpeedDial2 web Password, in plain text
	 * (as entered on https://speeddial2.com/login) */
	private static $password = '';


	static function getUsername() {
		if (gettype(self::$username) != "string" || strlen(self::$username) == 0) {
			self::outputMissingAuthInfoError('username');
			exit;
		}

		return self::$username;
	}

	static function getPassword() {
		if (gettype(self::$password) != "string" || strlen(self::$password) == 0) {
			self::outputMissingAuthInfoError('password');
			exit;
		}

		return self::$password;
	}

	private static function outputMissingAuthInfoError($authFieldName) {
		print('

		<div style="background: #ffb3b3; color: #000; font-family: sans-serif;
		 font-size: 16px; padding: 30px; margin: 30px; border: 2px solid #800">

			<div style="font-weight: bold; font-size: 24px; margin-bottom: 15px;">
				Speed Reader error &mdash; Missing authentication info
			</div>

			<div>
				<p>
					One or more required authentication fields to download your
					stored groups/dials from Speed Dial 2 servers are not set.
				</p>
				<p>
					Please edit the file
					<span style="font-family: monospace; font-weight: bold">
						Auth.class.inc
					</span>
					and set the values of
					<span style="font-family: monospace; font-weight: bold">
						Auth::$'.$authFieldName.'
					</span>
					and any other missing field to match your login credentials
					for Speed Dial 2 (as entered on the
					<a href="https://speeddial2.com/login">Speed Dial 2 Login</a>
					page).
			</div>

		</div>

		');
	}

}




