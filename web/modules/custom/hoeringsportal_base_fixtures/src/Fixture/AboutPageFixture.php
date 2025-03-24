<?php

namespace Drupal\hoeringsportal_base_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * About page fixture.
 *
 * @package Drupal\hoeringsportal_hearing_fixtures\Fixture
 */
final class AboutPageFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {
    $page = Node::create([
      'type' => 'static_page',
      'title' => 'Om deltag.aarhus.dk',
      'field_teaser' => 'Det her er siden, hvor du kan orientere dig om, hvordan du kan deltage som borger, virksomhed eller organisation vedrørende den aktuelle udvikling i Aarhus Kommune. ',
    ]);

    $paragraph = Paragraph::create([
      'type' => 'text',
      'field_content_block_text' => [
        'value' => <<<'BODY'
<p>Det her er siden, hvor du kan orientere dig om, hvordan du kan deltage som borger, virksomhed eller organisation vedrørende den aktuelle udvikling i Aarhus Kommune. Du kan her danne dig et overblik over, hvor og hvordan du kan deltage og være med.&nbsp;</p>
<p>I Aarhus Kommune ønsker vi at samarbejde med dig om forskellige initiativer – det kan være, når vi udvikler en ny bydel eller udformer en ny politik. Det er forskelligt, hvordan du kan deltage og være med afhængig af emnet. Nogle gange vil det være i form af en åben debat, andre gange kan du bidrage med et svar i en <a href="https://deltag.aarhus.dk/hoering">høring</a>. Og nogle gange noget helt tredje.</p>
<p>Deltagelsesportalen er ganske ny og blev lanceret i efteråret 2018, hvor vi startede med en række pilotcases.&nbsp;</p>
<p>Vi håber, at du her på siden vil være nysgerrig og har lyst til at deltage og være med!</p>
BODY,
        'format' => 'filtered_html',
      ],
    ]);
    $paragraph->save();
    $pageParagraphs[] = $paragraph;

    $page->set('field_section', array_map(
      static fn(Paragraph $paragraph) => [
        'target_id' => $paragraph->id(),
        'target_revision_id' => $paragraph->getRevisionId(),
      ],
      $pageParagraphs
    ));

    $page->save();
    $this->addReference('node:static_page:About', $page);
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies() {
    return [
      MediaFixture::class,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['node', 'static_page'];
  }

}
