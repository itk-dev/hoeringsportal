<?php

namespace Drupal\hoeringsportal_citizen_proposal\Helper;

use Drupal\entity_events\EntityEventType;
use Drupal\entity_events\Event\EntityEvent;
use Drupal\hoeringsportal_citizen_proposal\Helper\Helper as CitizenProposalHelper;
use Drupal\node\NodeInterface;
use Drupal\symfony_mailer\EmailFactoryInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Mail helper for citizen proposal archiving.
 */
final class MailHelper implements EventSubscriberInterface, LoggerAwareInterface, LoggerInterface {
  use LoggerAwareTrait;
  use LoggerTrait;

  private const MAILER_TYPE = 'hoeringsportal_citizen_proposal';
  public const MAILER_SUBTYPE_PROPOSAL_CREATED_CITIZEN = 'proposal_created_citizen';
  public const MAILER_SUBTYPE_PROPOSAL_CREATED_EDITOR = 'proposal_created_editor';
  public const MAILER_SUBTYPE_PROPOSAL_PUBLISHED_CITIZEN = 'proposal_published_citizen';

  /**
   * Constructor.
   */
  public function __construct(
    readonly private CitizenProposalHelper $citizenProposalHelper,
    readonly private EmailFactoryInterface $emailFactory,
    LoggerInterface $logger,
  ) {
    $this->setLogger($logger);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      EntityEventType::INSERT => 'handle',
      EntityEventType::UPDATE => 'handle',
    ];
  }

  /**
   * Event handler (cf. self::getSubscribedEvents()).
   *
   * @see self::getSubscribedEvents()
   */
  public function handle(EntityEvent $event) {
    $entity = $event->getEntity();
    if ($entity instanceof NodeInterface
      && $this->citizenProposalHelper->isCitizenProposal($entity)) {
      $this->sendMails($entity, $event->getEventType());
    }
  }

  /**
   * Send mail.
   *
   * @return \Drupal\symfony_mailer\EmailInterface[]
   *   The emails.
   */
  public function sendMails(NodeInterface $proposal, string $eventType): array {
    $emails = [];

    if (EntityEventType::INSERT === $eventType) {
      $emails[] = $this->emailFactory->sendTypedEmail(self::MAILER_TYPE,
        self::MAILER_SUBTYPE_PROPOSAL_CREATED_CITIZEN, $proposal);
      $emails[] = $this->emailFactory->sendTypedEmail(self::MAILER_TYPE,
        self::MAILER_SUBTYPE_PROPOSAL_CREATED_EDITOR, $proposal);
    }
    elseif (EntityEventType::UPDATE === $eventType) {
      /** @var ?NodeInterface $proposalOriginal */
      $proposalOriginal = $proposal->original;
      if ($proposal->isPublished() && !$proposalOriginal?->isPublished()) {
        $emails[] = $this->emailFactory->sendTypedEmail(self::MAILER_TYPE,
          self::MAILER_SUBTYPE_PROPOSAL_PUBLISHED_CITIZEN, $proposal);
      }
    }

    return $emails;
  }

  /**
   * {@inheritdoc}
   */
  public function log($level, $message, array $context = []): void {
    $this->logger->log($level, $message, $context);
  }

}
