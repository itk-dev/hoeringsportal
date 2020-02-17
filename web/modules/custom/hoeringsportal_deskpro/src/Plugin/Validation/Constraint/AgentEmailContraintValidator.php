<?php

namespace Drupal\hoeringsportal_deskpro\Plugin\Validation\Constraint;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\EmailItem;
use Drupal\hoeringsportal_deskpro\Service\DeskproService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the AgentEmail constraint.
 */
class AgentEmailContraintValidator extends ConstraintValidator implements ContainerInjectionInterface {

  /**
   * Drupal\hoeringsportal_deskpro\Service\DeskproService definition.
   *
   * @var \Drupal\hoeringsportal_deskpro\Service\DeskproService
   */
  protected $deskpro;

  /**
   * {@inheritDoc}
   */
  public function __construct(DeskproService $deskpro) {
    $this->deskpro = $deskpro;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('hoeringsportal_deskpro.deskpro')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function validate($value, Constraint $constraint) {
    // This is a single-item field so we only need to
    // validate the first item.
    $item = $value->first();

    // If there is no value we don't need to validate anything.
    if (!isset($item) || !$item instanceof EmailItem) {
      return NULL;
    }

    $email = $item->value;

    $agents = $this->deskpro->getAgents(['no_cache' => 1]);

    foreach ($agents->getData() as $agent) {
      if ($email === $agent['primary_email']) {
        return;
      }
    }

    $this->context->addViolation($constraint->invalidAgentEmail, ['%email' => $email]);
  }

}
