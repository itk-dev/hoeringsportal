<?php

namespace Drupal\hoeringsportal_citizen_proposal\Plugin\EmailBuilder;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Template\TwigEnvironment;
use Drupal\hoeringsportal_citizen_proposal\Exception\RuntimeException;
use Drupal\hoeringsportal_citizen_proposal\Helper\Helper;
use Drupal\hoeringsportal_citizen_proposal\Helper\MailHelper;
use Drupal\node\NodeInterface;
use Drupal\symfony_mailer\EmailFactoryInterface;
use Drupal\symfony_mailer\EmailInterface;
use Drupal\symfony_mailer\MailerHelperTrait;
use Drupal\symfony_mailer\Processor\EmailBuilderBase;
use Drupal\symfony_mailer\Processor\TokenProcessorTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the Email Builder plug-in for the citizen proposal module.
 *
 * @see https://www.drupal.org/docs/contributed-modules/symfony-mailer-0/development/emailbuilder-development-override-existing
 *
 * @EmailBuilder(
 *   id = "hoeringsportal_citizen_proposal",
 *   sub_types = {
 *     "proposal_created_citizen" = @Translation("Proposal created (citizen)"),
 *     "proposal_created_editor" = @Translation("Proposal created (editor)"),
 *     "proposal_published_citizen" = @Translation("Proposal published (citizen)"),
 *   }
 * )
 */
final class CitizenEmailBuilder extends EmailBuilderBase implements ContainerFactoryPluginInterface {
  use MailerHelperTrait;
  use TokenProcessorTrait;

  /**
   * {@inheritdoc}
   */
  public function __construct(readonly private Helper $citizenProposalHelper, readonly private TwigEnvironment $twig, array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get(Helper::class),
      $container->get('twig'),
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function createParams(EmailInterface $email, NodeInterface $node = NULL) {
    if (NULL === $node
      || !$this->citizenProposalHelper->isCitizenProposal($node)) {
      throw new \TypeError('Node must be a citizen proposal');
    }
    $email->setParam('node', $node);
  }

  /**
   * {@inheritdoc}
   */
  public function fromArray(EmailFactoryInterface $factory, array $message) {
    return $factory->newTypedEmail($message['module'], $message['key'], $message['params']['node']);
  }

  /**
   * {@inheritdoc}
   */
  public function build(EmailInterface $email) {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $email->getParam('node');
    $to = $node->get('field_author_email')->value;
    $email->setTo($to);
    if (MailHelper::MAILER_SUBTYPE_PROPOSAL_CREATED_EDITOR === $email->getSubType()) {
      $to = $this->citizenProposalHelper->getAdminFormValue([
        'emails',
        'email_editor',
      ]);
      $email->setTo($to);
    }
    $email->setVariable('node', $node);

    $subType = $email->getSubType();
    $config = $this->citizenProposalHelper->getAdminFormValue([
      'emails',
      $subType,
    ]);
    if (!isset($config['subject'], $config['content']['value'])) {
      throw new RuntimeException(sprintf('Email %s/%s not configured.', $email->getType(), $subType));
    }
    ['subject' => $subject, 'content' => $content] = $config;
    $content['value'] = $this->twig->renderInline($content['value'], $email->getVariables());

    $email
      ->setSubject($subject, TRUE)
      ->setBody([
        '#type' => 'processed_text',
        '#text' => $content['value'],
        '#format' => $content['format'] ?? filter_default_format(),
      ]);

    parent::build($email);
  }

}
