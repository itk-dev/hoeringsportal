<?php

namespace Drupal\hoeringsportal_deskpro\Twig;

use Drupal\Core\Entity\EntityInterface;
use Drupal\hoeringsportal_deskpro\Service\DeskproService;

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
   * {@inheritdoc}
   */
  public function __construct(DeskproService $deskpro) {
    $this->deskpro = $deskpro;
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new \Twig_SimpleFunction('deskpro_ticket_form', [$this, 'getTicketForm'], ['is_safe' => ['all']]),
    ];
  }

  /**
   * Get ticket form.
   */
  public function getTicketForm(EntityInterface $node) {
    try {
      if ($node->bundle() !== 'hearing') {
        throw new \Exception('Node of type hearing expected. Got' . $node->bundle());
      }
      $departmentId = 1;
      $hearingId = 1;
      $defaultValues = [];

      $user = \Drupal::currentUser();
      if ($user->isAuthenticated()) {
        $defaultValues['ticket']['person']['user_name'] = $user->getAccountName();
        $defaultValues['ticket']['person']['user_email']['email'] = $user->getEmail();
      }

      $form = $this->deskpro->getTicketEmbedForm($departmentId, $hearingId, $defaultValues);

      return $form;
    }
    catch (\Exception $e) {
      return '<pre style="background: red; padding: 1em; color: yellow">' . htmlspecialchars($e->getMessage()) . '</pre>';
    }
  }

}
