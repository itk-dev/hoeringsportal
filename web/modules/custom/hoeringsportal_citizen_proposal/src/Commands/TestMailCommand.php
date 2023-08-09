<?php

namespace Drupal\hoeringsportal_citizen_proposal\Commands;

use Drupal\entity_events\EntityEventType;
use Drupal\hoeringsportal_citizen_proposal\Helper\Helper;
use Drupal\hoeringsportal_citizen_proposal\Helper\MailHelper;
use Drupal\symfony_mailer\AddressInterface;
use Drush\Commands\DrushCommands as BaseDrushCommands;

/**
 * Test mail commands for citizen proposal.
 */
class TestMailCommand extends BaseDrushCommands {
  /**
   * Constructor for the citizen proposal commands class.
   */
  public function __construct(
    readonly private Helper $helper,
    readonly private MailHelper $mailHelper
  ) {
    parent::__construct();
  }

  /**
   * Send mail command.
   *
   * @param int $proposalId
   *   The proposal (node) id.
   * @param string $event
   *   The event; "create" or "update".
   * @param string $recipient
   *   The mail recipient.
   *
   * @command hoeringsportal-citizen-proposal:test-mail:send
   */
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
