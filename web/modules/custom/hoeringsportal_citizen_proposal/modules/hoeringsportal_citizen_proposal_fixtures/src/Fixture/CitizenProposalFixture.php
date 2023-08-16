<?php

namespace Drupal\hoeringsportal_citizen_proposal_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\hoeringsportal_base_fixtures\Fixture\MediaFixture;
use Drupal\hoeringsportal_base_fixtures\Fixture\ParagraphFixture;
use Drupal\hoeringsportal_base_fixtures\Helper\Helper as BaseFixtureHelper;
use Drupal\hoeringsportal_citizen_proposal\Helper\Helper;
use Drupal\hoeringsportal_citizen_proposal\Helper\MailHelper;
use Drupal\hoeringsportal_citizen_proposal_archiving\Helper\Helper as ArchiveHelper;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Page fixture.
 *
 * @package Drupal\hoeringsportal_citizen_proposal_fixtures\Fixture
 */
class CitizenProposalFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  /**
   * Constructor.
   */
  public function __construct(
    readonly private BaseFixtureHelper $baseFixtureHelper,
    readonly private Helper $helper,
    EventDispatcherInterface $eventDispatcher,
    MailHelper $mailHelper,
    ArchiveHelper $archivingHelper
  ) {
    // Prevent sending notification emails.
    $eventDispatcher->removeSubscriber($mailHelper);
    $eventDispatcher->removeSubscriber($archivingHelper);
  }

  /**
   * {@inheritdoc}
   */
  public function load() {
    // Citizen proposal nodes.
    $entity = Node::create([
      'type' => 'citizen_proposal',
      'title' => 'Borgerforslag nummer 1',
      'status' => NodeInterface::PUBLISHED,
      'field_author_uuid' => '1111',
      'field_author_name' => 'Anders And',
      'field_author_email' => 'anders.and@itkdev.dk',
      'field_vote_start' => DrupalDateTime::createFromFormat('U', strtotime('tomorrow'))->format('Y-m-d\TH:i:s'),
      'field_vote_end' => DrupalDateTime::createFromFormat('U', strtotime('tomorrow +3 months'))->format('Y-m-d\TH:i:s'),
      'field_content_state' => 'upcoming',
      'field_proposal' => [
        'value' => $this->baseFixtureHelper->getText('filteredHtmlShort.html'),
        'format' => 'filtered_html',
      ],
      'field_remarks' => [
        'value' => $this->baseFixtureHelper->getText('filteredHtmlShort.html'),
        'format' => 'filtered_html',
      ],
    ]);
    $entity->save();
    $this->addReference('node:citizen_proposal:Proposal1', $entity);

    // Add some support.
    for ($i = 0; $i < 87; $i++) {
      $this->helper->saveSupport(uniqid('', TRUE), $entity, ['user_name' => self::class]);
    }

    $entity = Node::create([
      'type' => 'citizen_proposal',
      'title' => 'Borgerforslag nummer 2',
      'status' => NodeInterface::PUBLISHED,
      'field_author_uuid' => '2222',
      'field_author_name' => 'Fedtmule',
      'field_author_email' => 'fedtmule@itkdev.dk',
      'field_vote_start' => DrupalDateTime::createFromFormat('U', strtotime('yesterday -3 months'))->format('Y-m-d\TH:i:s'),
      'field_vote_end' => DrupalDateTime::createFromFormat('U', strtotime('yesterday'))->format('Y-m-d\TH:i:s'),
      'field_content_state' => 'finished',
      'field_proposal' => [
        'value' => $this->baseFixtureHelper->getText('filteredHtml1.html'),
        'format' => 'filtered_html',
      ],
      'field_remarks' => [
        'value' => $this->baseFixtureHelper->getText('filteredHtmlLong.html'),
        'format' => 'filtered_html',
      ],
    ]);
    $entity->save();
    $this->addReference('node:citizen_proposal:Proposal2', $entity);

    // Add some support.
    for ($i = 0; $i < 3; $i++) {
      $this->helper->saveSupport(uniqid('', TRUE), $entity, ['user_name' => self::class]);
    }

    $entity = Node::create([
      'type' => 'citizen_proposal',
      'title' => 'Borgerforslag nummer 3',
      'status' => NodeInterface::NOT_PUBLISHED,
      'field_author_uuid' => '3333',
      'field_author_name' => 'Hexia De Trick',
      'field_author_email' => 'givmiglykkemønten@itkdev.dk',
      'field_vote_start' => DrupalDateTime::createFromFormat('U', strtotime('-1 month'))->format('Y-m-d\TH:i:s'),
      'field_vote_end' => DrupalDateTime::createFromFormat('U', strtotime('+2 months'))->format('Y-m-d\TH:i:s'),
      'field_content_state' => 'active',
      'field_proposal' => [
        'value' => $this->baseFixtureHelper->getText('filteredHtmlLong.html'),
        'format' => 'filtered_html',
      ],
      'field_remarks' => [
        'value' => $this->baseFixtureHelper->getText('filteredHtmlLong.html'),
        'format' => 'filtered_html',
      ],
    ]);
    $entity->save();
    $this->addReference('node:citizen_proposal:Proposal3', $entity);

    // Set admin values.
    $data = Yaml::parseFile(__DIR__ . '/CitizenProposalFixture/citizen_proposal_admin_form_values.yaml');
    if (isset($data['citizen_proposal_admin_form_values'])) {
      $this->helper->setAdminValues($data['citizen_proposal_admin_form_values']);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies() {
    return [
      MediaFixture::class,
      ParagraphFixture::class,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['node'];
  }

}
