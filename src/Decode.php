<?php

namespace Websyspro\Jwt;

use Exception;
use stdClass;

Class Decode
{
  public object $payload;
  public bool $verified;

  public function __construct(
    private string $jwt,
    private string $key
  ){
    $this->Decoding();
    $this->Clears();
  }

  private function urlsafeB64Decode(
    string $input
  ): string {
    return base64_decode(
      Decode::convertBase64UrlToBase64($input)
    );
  }

  public function convertBase64UrlToBase64(
    string $input
  ): string {
    $remainder = strlen($input) % 4;
    if($remainder){
      $padlen = 4 - $remainder;
      $input .= str_repeat(
        "=", $padlen
      );
    }

    return strtr(
      $input, "-_", "+/"
    );
  }
  
  public function jsonDecode(
    string $input
  ): mixed {
    $obj = json_decode(
      $input, false, 512, JSON_BIGINT_AS_STRING
    );

    if (json_last_error()) {
      throw new Exception("Syntax error, malformed JSON");
    } elseif ($obj === null && $input !== 'null') {
      throw new Exception("Null result with non-null input");
    }

    return $obj;
  }

  private function verify(
    string $msg,
    string $cryptor,
    string $key
  ): bool {
    return openssl_verify(
      $msg, $this->UrlsafeB64Decode($cryptor), $key, "SHA256"
    ) === 1;
  }  

  private function decoding(
  ): void {
    [$head, $body, $cryptor] = (
      explode(".", $this->jwt)
    );

    $this->payload = (object)(
      $this->jsonDecode(
        $this->urlsafeB64Decode(
          $body
        )
      )
    );

    $this->verified = $this->verify(
      "{$head}.{$body}", $cryptor, $this->key
    );

    $time = time();

    if(isset($this->payload->jti) === false){
      $this->verified = false;
    } else
    if(isset($this->payload->nbf) === true && $this->payload->nbf > $time){
      $this->verified = false;
    } else
    if(isset($this->payload->iat) === true && isset($this->payload->exp)){
      if($this->payload->iat > $time){
        $this->verified = false;
      }

      if($this->payload->exp <= $time){
        $this->verified = false;
      }
    }
  }

  public function clears(
  ): void {
    unset($this->jwt);
    unset($this->key);
  }
}