<?php

namespace Drupal\hoeringsportal_citizen_proposal_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\hoeringsportal_base_fixtures\Fixture\MediaFixture;
use Drupal\hoeringsportal_base_fixtures\Fixture\ParagraphFixture;
use Drupal\hoeringsportal_base_fixtures\Helper\Helper;
use Drupal\node\Entity\Node;

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
    readonly private Helper $baseFixtureHelper
  ) {}

  /**
   * {@inheritdoc}
   */
  public function load() {
    // Citizen proposal nodes.
    $entity = Node::create([
      'type' => 'citizen_proposal',
      'title' => 'Borgerforslag nummer 1',
      'status' => 1,
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

    $entity = Node::create([
      'type' => 'citizen_proposal',
      'title' => 'Borgerforslag nummer 2',
      'status' => 1,
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

    $entity = Node::create([
      'type' => 'citizen_proposal',
      'title' => 'Borgerforslag nummer 3',
      'status' => 1,
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
