<?php

namespace Drupal\hoeringsportal_deskpro\Twig;

use Drupal\hoeringsportal_deskpro\Service\DeskproService;
use Drupal\hoeringsportal_deskpro\Service\HearingHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Custom Twig extensions for Høringsportal.
 */
class TwigExtension extends AbstractExtension {
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
      new TwigFunction(
        'deskpro_ticket_custom_field',
        [
          $this,
          'getTicketCustomField',
        ],
        [
          'is_safe' => ['all'],
        ]
      ),
      new TwigFunction(
        'deskpro_get_ticket_count',
        [
          $this->helper,
          'getHearingTicketsCount',
        ],
        [
          'is_safe' => ['all'],
        ]
      ),
      new TwigFunction(
        'deskpro_get_tickets',
        [
          $this->helper,
          'getHearingTickets',
        ],
        [
          'is_safe' => ['all'],
        ]
      ),
    ];
  }

  /**
   * Get ticket custom field value.
   */
  public function getTicketCustomField(array $ticket, string $field) {
    $config = $this->helper->getDeskproConfig();

    if ('representation' === $field) {
      $representations = $config->getRepresentations();
      // The 'representation' value (if set) is a array indexed by the
      // representation type key.
      $index = array_key_first((array) ($ticket['fields'][$field] ?? []));

      return $representations[$index]['title'] ?? NULL;
    }

    return $ticket['fields'][$field] ?? NULL;
  }

}
