<?php

namespace Drupal\hoeringsportal_deskpro\Twig;

use Drupal\hoeringsportal_deskpro\Service\DeskproService;
use Drupal\hoeringsportal_deskpro\Service\HearingHelper;

/**
 * Custom Twig extensions for Høringsportal.
 */
class TwigExtension extends \Twig_Extension {
  /**
   * The Deskpro service.
   *
   * @var \Drupal\hoeringsportal_deskpro\Service\DeskproService
   */
  private $deskpro;

  /**
   * The Deskpro helper.
   *
   * @var \Drupal\hoeringsportal_deskpro\Service\HearingHelper
   */
  private $helper;

  /**
   * {@inheritdoc}
   */
  public function __construct(DeskproService $deskpro, HearingHelper $helper) {
    $this->deskpro = $deskpro;
    $this->helper = $helper;
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new \Twig_SimpleFunction('deskpro_ticket_custom_field', [$this, 'getTicketCustomField'], ['is_safe' => ['all']]),
    ];
  }

  /**
   * Get ticket custom field value.
   */
  public function getTicketCustomField(array $ticket, string $field) {
    $config = $this->helper->getDeskproConfig();

    if ('representation' === $field) {
      $representations = $config->getRepresentations();
      $index = $ticket['fields'][$field][0] ?? NULL;
      return $representations[$index]['title'] ?? NULL;
    }

    return $ticket['fields'][$field] ?? NULL;
  }

}
