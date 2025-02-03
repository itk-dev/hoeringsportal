<?php

namespace Drupal\hoeringsportal_data\Service;

/**
 * Danmarks Adressers Web API (DAWA) service.
 */
class DAWA { // phpcs:ignore

  /**
   * Get coordinates from an address.
   *
   * @param string $address
   *   The address.
   * @param string $type
   *   Either 'adgangspunkt' or 'vejpunkt'
   *   (cf. https://dawa.aws.dk/dok/api/adgangsadresse#databeskrivelse)
   *
   * @return null|array
   *   The coordinates of the address if unique.
   */
  public function getCoordinates($address, $type = 'adgangspunkt') {
    $address = trim($address);
    if (empty($address)) {
      return NULL;
    }

    $url = 'https://dawa.aws.dk/adgangsadresser?' . http_build_query(['q' => $address]);
    $response = \Drupal::httpClient()->get($url);

    if (200 !== $response->getStatusCode()) {
      return NULL;
    }

    $content = \json_decode((string) $response->getBody(), TRUE);

    if (NULL !== $content && \is_array($content) && 1 === \count($content)) {
      $item = reset($content);
      if (isset($item[$type]['koordinater'])) {
        return $item[$type]['koordinater'];
      }
    }

    return NULL;
  }

}
