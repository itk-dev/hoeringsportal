<?php

namespace Drupal\hoeringsportal_citizen_proposal\Drush\Commands;

use Drupal\entity_events\EntityEventType;
use Drupal\hoeringsportal_citizen_proposal\Helper\Helper;
use Drupal\hoeringsportal_citizen_proposal\Helper\MailHelper;
use Drupal\symfony_mailer\AddressInterface;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands as BaseDrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Test mail commands for citizen proposal.
 */
final class TestMailCommand extends BaseDrushCommands {

  /**
   * Constructor for the citizen proposal commands class.
   */
  public function __construct(
    readonly private Helper $helper,
    readonly private MailHelper $mailHelper,
  ) {
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get(Helper::class),
      $container->get(MailHelper::class)
    );
  }

  /**
   * Send mail command.
   */
  #[CLI\Command(name: 'hoeringsportal-citizen-proposal:test-mail:send')]
  #[CLI\Argument(name: 'proposalId', description: 'The proposal (node) id.')]
  #[CLI\Argument(name: 'event', description: 'The event; "create" or "update".', suggestedValues: ['create', 'update'])]
  #[CLI\Argument(name: 'recipient', description: 'The mail recipient.')]
  public function send(int $proposalId, string $event, string $recipient): void {
    $proposal = $this->helper->loadCitizenProposal($proposalId);
    // Overwrite the proposal author.
    $proposal->get('field_author_email')->setValue($recipient);

    $eventType = 'create' === $event ? EntityEventType::INSERT : EntityEventType::UPDATE;
    if (EntityEventType::UPDATE === $eventType) {
      // Simulate that proposal changes from unpublished to published.
      $proposal->original = clone $proposal;
      $proposal->original->setUnpublished();
      $proposal->setPublished();
    }
    $emails = $this->mailHelper->sendMails($proposal, $eventType);

    foreach ($emails as $email) {
      if ($error = $email->getError()) {
        $this->writeln($error);
      }
      else {
        $this->writeln(sprintf('To: %s', implode(
          ', ',
          array_map(
            static fn(AddressInterface $address) => $address->getEmail(),
            $email->getTo()
          )
        )));
        $this->writeln(sprintf('Subject: %s', $email->getSubject()));
        $this->writeln('HMTL:');
        $this->writeln($email->getHtmlBody());
        $this->writeln(str_repeat('-', 80));
        $this->writeln('Text:');
        $this->writeln($email->getTextBody());
      }
    }
  }

}
