<?php
namespace App\Model\Behavior;

use Cake\ORM\Behavior;
use Cake\Core\Configure;

class FlatIronDataBaseBehavior extends Behavior {

    public function encrypt($decrypted) {
        $salt = Configure::read('Security.salt');
        $key = hash('SHA256', $salt, true);
        // Build $iv and $iv_base64.  We use a block size of 128 bits (AES compliant) and CBC mode.  (Note: ECB mode is inadequate as IV is not used.)
        srand(); $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('AES-128-CBC'));
        if (strlen($iv_base64 = rtrim(base64_encode($iv), '=')) != 22) return false;
        // Encrypt $decrypted and an MD5 of $decrypted using $key.  MD5 is fine to use here because it's just to verify successful decryption.
        $encrypted = base64_encode(openssl_encrypt($decrypted . md5($decrypted), 'AES-128-CBC', $key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING, $iv));
        // Done!
        return $iv_base64 . $encrypted;
     }

    public function decrypt($encrypted) {
        $salt = Configure::read('Security.salt');
        $key = hash('SHA256', $salt, true);
        // Retrieve $iv which is the first 22 characters plus ==, base64_decoded.
        $iv = base64_decode(substr($encrypted, 0, 22) . '==');
        // Remove $iv from $encrypted.
        $encrypted = substr($encrypted, 22);
        // Decrypt the data.  rtrim won't corrupt the data because the last 32 characters are the md5 hash; thus any \0 character has to be padding.
        $decrypted = rtrim(openssl_decrypt($encrypted, "AES-128-CBC", $key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING, $iv), "\0\4");
        // Retrieve $hash which is the last 32 characters of $decrypted.
        $hash = substr($decrypted, -32);
        // Remove the last 32 characters from $decrypted.
        $decrypted = substr($decrypted, 0, -32);
        // Integrity check.  If this fails, either the data is corrupted, or the password/salt was incorrect.
        if (md5($decrypted) != $hash) return false;
        // Done!
        return $decrypted;
    }
}