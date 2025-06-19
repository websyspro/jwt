<?php

namespace Websyspro\Jwt;

use Websyspro\Commons\Util;

class Encode
{
  private array $jwt;

  public function __construct(
    private array $payload,
    private string $key
  ){
    $this->Generation();
  }

  private function Header(
  ): array {
    return [
      "typ" => "JWT",
      "alg" => "RS256" 
    ];
  }

  private function UUID(
  ): string {
    $data = random_bytes(16);

    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

    return strtoupper(
      vsprintf(
        "%s%s-%s-%s-%s-%s%s%s", str_split(
          bin2hex($data), 4
        )
      )
    );
  }  

  private function Payload(
  ): array {
    return $this->payload;
  }

  private function DefaultPayload(
  ): array {
    $time = time();

    return [
      "jti" => $this->UUID(),
      "nbf" => $time,
      "iat" => $time,
      "exp" => $time + 3600,
      "data" => $this->Payload()
    ];
  }

  private function JSONEncode(
    array $input
  ): string {
    return (string)json_encode(
      $input, 64
    );
  }

  private function UrlsafeB64Encode(
    string $input  
  ): string {
    return str_replace(
      "=", "", strtr(
        base64_encode(
          $input
        ), "+/", "-_"
      )
    );
  }

  private function Generation(
  ): void {
    $this->jwt[] = $this->UrlsafeB64Encode(
      $this->JSONEncode(
        $this->Header()
      )
    );
    
    $this->jwt[] = $this->UrlsafeB64Encode(
      $this->JSONEncode(
        $this->DefaultPayload()
      )
    );

    $this->jwt[] = $this->UrlsafeB64Encode(
      Sign::Set(
        Util::Join(
          ".", $this->jwt
        ), $this->key, "SHA256"
      )
    );
  }
  
  public function Get(
  ): string {
    return Util::Join(
      ".", $this->jwt
    );
  }
}