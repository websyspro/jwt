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

  private function header(
  ): array {
    return [
      "typ" => "JWT",
      "alg" => "RS256" 
    ];
  }

  private function uuid(
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

  private function payload(
  ): array {
    return $this->payload;
  }

  private function defaultPayload(
  ): array {
    $time = time();

    return [
      "jti" => $this->uuid(),
      "nbf" => $time,
      "iat" => $time,
      "exp" => $time + 3600,
      "data" => $this->payload()
    ];
  }

  private function jsonEncode(
    array $input
  ): string {
    return (string)json_encode(
      $input, 64
    );
  }

  private function urlsafeB64Encode(
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

  private function generation(
  ): void {
    $this->jwt[] = $this->urlsafeB64Encode(
      $this->jsonEncode(
        $this->header()
      )
    );
    
    $this->jwt[] = $this->urlsafeB64Encode(
      $this->jsonEncode(
        $this->defaultPayload()
      )
    );

    $this->jwt[] = $this->urlsafeB64Encode(
      Sign::set(
        Util::join(
          ".", $this->jwt
        ), $this->key, "SHA256"
      )
    );
  }
  
  public function get(
  ): string {
    return Util::Join(
      ".", $this->jwt
    );
  }
}