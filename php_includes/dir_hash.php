<?php
define('SALT', 'SocialWeb');
// function dir_encrypt($text)
// {
//     return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, SALT, $text, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))));
// }
// function dir_decrypt($text)
// {
//     return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, SALT, base64_decode($text), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));
// }

function dir_encrypt ($stringArray, $key = "Soc") {
 $s = strtr(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), serialize($stringArray), MCRYPT_MODE_CBC, md5(md5($key)))), '+/=', '_12');
 return $s;
}

function dir_decrypt ($stringArray, $key = "Soc") {
 $s = unserialize(rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode(strtr($stringArray, '_12', '+/=')), MCRYPT_MODE_CBC, md5(md5($key))), "\0"));
 return $s;
}

//$encryptedmessage = encrypt("your message");
//echo decrypt($encryptedmessage);
?>
