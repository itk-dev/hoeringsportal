<?php

namespace Drupal\hoeringsportal_project_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\hoeringsportal_base_fixtures\Fixture\MediaFixture;
use Drupal\hoeringsportal_base_fixtures\Fixture\ParagraphFixture;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Page fixture.
 *
 * @package Drupal\hoeringsportal_project_fixtures\Fixture
 */
class ProjectPageFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {
    // Project main page.
    $entity = Node
               ::create([
                 'type' => 'project_page',
               ])
                 ->setTitle('Hvad er byudvikling?')
                 ->set('field_project_category', [
                   $this->getReference('project_categories:Byudvikling'),
                 ]);

    $paragraph = Paragraph
                  ::create([
                    'type' => 'content_block',
                  ])
                    ->set('field_paragraph_title', 'Det er et godt spørgsmål …')
                    ->set('field_content_block_text', [
                      'value' => <<<'BODY'
<p>Byudvikling refererer til planlægning, design og realisering af forbedringer og ændringer i en by eller et byområde. Det indebærer typisk en bred vifte af aktiviteter og processer, herunder udvikling af infrastruktur, bygningsprojekter, zonestyring, miljøbeskyttelse, social integration og økonomisk udvikling. Formålet med byudvikling er at skabe mere bæredygtige, attraktive og funktionelle bymiljøer, der imødekommer behovene hos både de nuværende og fremtidige indbyggere. Det kan omfatte alt fra renovering af eksisterende bygninger og offentlige rum til opførelse af nye boliger, virksomheder, parker og rekreative områder.</p>
BODY,
                      'format' => 'filtered_html',
                    ]);
    $paragraph->save();
    $entity->field_content_sections->appendItem([
      'target_id' => $paragraph->id(),
      'target_revision_id' => $paragraph->getRevisionId(),
    ]);

    $entity->save();
    $this->addReference('project_page:Hvad er byudvikling?', $entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies() {
    return [
      ProjectCategoryFixture::class,
      BlockContentFixture::class,
      MediaFixture::class,
      ParagraphFixture::class,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['node', 'project'];
  }

}
