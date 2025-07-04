<?php

namespace Websyspro\Jwt;

use Exception;

class Sign
{
  public static function set(
    string $signingInput,
    string $key,
    string $algorithm,
    string $signature = ""
  ): string {
    if(is_resource($key) === false && openssl_pkey_get_private($key) === false){
      throw new Exception("OpenSSL unable to validate key");
    }

    if(openssl_sign($signingInput, $signature, $key, $algorithm) === false){
      throw new Exception("OpenSSL unable to sign data");
    }

    return $signature;
  }
}