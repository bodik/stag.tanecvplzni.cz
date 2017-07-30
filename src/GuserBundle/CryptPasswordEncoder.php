<?php
 
namespace GuserBundle;

use Symfony\Component\Security\Core\Encoder\BasePasswordEncoder;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
 
class CryptPasswordEncoder extends BasePasswordEncoder {
	const SALT_LENGTH = 8;
	const CRYPT_ALGO = "$6$";
	const SPECIAL_CHARS = '!@#$%^&*()\-_=+{};:,<.>';

	const PASSWORD_MIN_LENGTH = 12;
	const PASSWORD_MIN_CLASSES = 3;

	const PASSWORD_RET_OK = 0;
	const PASSWORD_RET_LENGTH = 1;
	const PASSWORD_RET_CLASSES = 2;
	const PASSWORD_RET_INCLUDESUSERNAME = 3;

	public function encodePassword($raw, $salt) {
		if(!empty($salt)) { 
			$asalt = $salt; 
		} else { 
			$asalt = self::CRYPT_ALGO . bin2hex(random_bytes(self::SALT_LENGTH)) . "$"; 
		}
		return crypt($raw, $asalt);
	}

	public function demergePasswordAndSalt($mergedPasswordSalt) {
		if( preg_match('/^(?<salt>\$.*\$.*\$)(?<password>.*)$/', $mergedPasswordSalt, $matches) == 1 ) {
			return [$matches["password"], $matches["salt"]];
		} else {
			return [$mergedPasswordSalt, null];
		}
	}

	public function isPasswordValid($encoded, $raw, $salt) {
		return hash_equals($this->encodePassword($raw, $salt), $encoded);
	}



	public function isPasswordStrength($username, $password) {

		# length
		if( strlen($password) < self::PASSWORD_MIN_LENGTH ) { return self::PASSWORD_RET_LENGTH;	}

		# classes
	        #http://stackoverflow.com/questions/2637896/php-regex-for-strong-password-validation	
		$r1='/[A-Z]/'; #Uppercase
		$r2='/[a-z]/'; #lowercase
		$r4='/[0-9]/'; #numbers
		$r3='/['.self::SPECIAL_CHARS.']/'; #whatever you mean by 'special char'
		$classes = 0;
		if(preg_match_all($r1,$password)>0) $classes++;
		if(preg_match_all($r2,$password)>0) $classes++;
		if(preg_match_all($r3,$password)>0) $classes++;
		if(preg_match_all($r4,$password)>0) $classes++;
		if($classes < self::PASSWORD_MIN_CLASSES) { return self::PASSWORD_RET_CLASSES; }
		
		# based on username
		if( strpos( strtolower($password), strtolower($username) ) !== False ) {	return self::PASSWORD_RET_INCLUDESUSERNAME; }

		return self::PASSWORD_RET_OK;
	}
}
