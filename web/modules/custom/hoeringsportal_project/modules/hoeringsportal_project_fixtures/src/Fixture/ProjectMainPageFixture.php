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
 * Landing page fixture.
 *
 * @package Drupal\hoeringsportal_project_fixtures\Fixture
 */
class ProjectMainPageFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {
    // Project main page.
    $entity = Node
      ::create([
        'type' => 'project_main_page',
      ])
        ->setTitle('Project')
        ->set('field_short_description', 'This is the first project')
        ->set('field_project_image', [
        ['target_id' => $this->getReference('media:Medium1')->id()],
        ])
        ->set('body', [
          'value' => <<<'BODY'
<p>This project is the very first project, and will focus on some <strong>stuff</strong>.</p>
BODY,
          'format' => 'filtered_html',
        ])
        ->set('field_aside_block', [
        ['target_id' => $this->getReference('block_content:aside_contact_info:the_main_address')->id()],
        ]);

    $paragraph = Paragraph
      ::create([
        'type' => 'image',
      ])
        ->set('field_paragraph_image', [
        ['target_id' => $this->getReference('media:Medium2')->id()],
        ]);
    $paragraph->save();
    $entity->field_content_sections->appendItem([
      'target_id' => $paragraph->id(),
      'target_revision_id' => $paragraph->getRevisionId(),
    ]);

    $paragraph = Paragraph
      ::create([
        'type' => 'info_box',
      ])
        ->set('field_paragraph_title', 'Important!')
        ->set('field_content_block_text', [
          'value' => <<<'BODY'
<p>Beware that this is the <em>first</em> project.</p>
BODY,
          'format' => 'filtered_html',
        ]);
    $paragraph->save();
    $entity->field_content_sections->appendItem([
      'target_id' => $paragraph->id(),
      'target_revision_id' => $paragraph->getRevisionId(),
    ]);

    $entity->save();
    $this->addReference('node:project_main_page:1', $entity);

    $entity = $entity->createDuplicate();
    $entity
      ->setTitle('Another project')
      ->set('field_short_description', 'We have more than one project')
      ->set('field_project_image', [
        ['target_id' => $this->getReference('media:Map1')->id()],
      ])
      ->set('body', [
        'value' => <<<'BODY'
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed luctus accumsan ante sit amet fermentum. Aliquam aliquet massa ut enim vulputate feugiat. Maecenas tincidunt risus rhoncus, interdum neque ac, aliquet ex. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Cras commodo, odio id scelerisque pulvinar, erat neque fringilla purus, non faucibus metus nibh ullamcorper arcu. In posuere magna ante, in cursus velit lacinia eu. Sed viverra blandit sem. Donec quam orci, tincidunt eget porttitor at, luctus eu est. In sed nulla mauris. Etiam arcu augue, accumsan vitae magna vel, interdum imperdiet magna.</p>
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed luctus accumsan ante sit amet fermentum. Aliquam aliquet massa ut enim vulputate feugiat. Maecenas tincidunt risus rhoncus, interdum neque ac, aliquet ex. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Cras commodo, odio id scelerisque pulvinar, erat neque fringilla purus, non faucibus metus nibh ullamcorper arcu. In posuere magna ante, in cursus velit lacinia eu. Sed viverra blandit sem. Donec quam orci, tincidunt eget porttitor at, luctus eu est. In sed nulla mauris. Etiam arcu augue, accumsan vitae magna vel, interdum imperdiet magna.
</p>
BODY,
        'format' => 'filtered_html',
      ]);
    $entity->save();
    $this->addReference('node:project_main_page:2', $entity);

    $entity = $entity->createDuplicate();
    $entity
      ->setTitle('The third project')
      ->set('field_short_description', 'We have more than one project')
      ->set('field_project_image', [
        ['target_id' => $this->getReference('media:Map1')->id()],
      ])
      ->set('body', [
        'value' => <<<'BODY'
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed luctus accumsan ante sit amet fermentum. Aliquam aliquet massa ut enim vulputate feugiat. Maecenas tincidunt risus rhoncus, interdum neque ac, aliquet ex. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Cras commodo, odio id scelerisque pulvinar, erat neque fringilla purus, non faucibus metus nibh ullamcorper arcu. In posuere magna ante, in cursus velit lacinia eu. Sed viverra blandit sem. Donec quam orci, tincidunt eget porttitor at, luctus eu est. In sed nulla mauris. Etiam arcu augue, accumsan vitae magna vel, interdum imperdiet magna.</p>
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed luctus accumsan ante sit amet fermentum. Aliquam aliquet massa ut enim vulputate feugiat. Maecenas tincidunt risus rhoncus, interdum neque ac, aliquet ex. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Cras commodo, odio id scelerisque pulvinar, erat neque fringilla purus, non faucibus metus nibh ullamcorper arcu. In posuere magna ante, in cursus velit lacinia eu. Sed viverra blandit sem. Donec quam orci, tincidunt eget porttitor at, luctus eu est. In sed nulla mauris. Etiam arcu augue, accumsan vitae magna vel, interdum imperdiet magna.
</p>
BODY,
        'format' => 'filtered_html',
      ])
      ->set('field_aside_block', [
        ['target_id' => $this->getReference('block_content:aside_contact_info:another_address')->id()],
      ]);
    ;
    $entity->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies() {
    return [
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
