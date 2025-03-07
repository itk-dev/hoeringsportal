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
    // Get actual representation used on a ticket.
    $getRepresentation = function () use ($ticket): ?array {
      $config = $this->helper->getDeskproConfig();
      $representations = $config->getRepresentations();

      $index = array_key_first((array) ($ticket['fields']['representation'] ?? []));

      return $representations[$index] ?? NULL;
    };

    if ('representation' === $field) {
      return $getRepresentation()['title'] ?? NULL;
    }

    if ('organization' === $field) {
      $representation = $getRepresentation();

      return ($representation['require_organization'] ?? FALSE)
        // Organization may not actually be set on ticket (!)
        ? ($ticket['fields'][$field] ?? NULL)
        : NULL;
    }

    return $ticket['fields'][$field] ?? NULL;
  }

}
