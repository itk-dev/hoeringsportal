<?php

namespace Drupal\hoeringsportal_hearing;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Component\Uuid\Uuid;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Template\Attribute;

/**
 * Token helper.
 */
final class TokenHelper {
  use StringTranslationTrait;

  /**
   * Token info.
   *
   * @implements hook_token_info().
   */
  public function tokenInfo(): array {
    return [
      'types' => [
        'gis' => [
          'name' => $this->t('GIS data'),
        ],
      ],
      'tokens' => [
        'gis' => [
          'minimap:?' => [
            'name' => $this->t('Insert GIS minimap by UUID'),
            'description' => $this->t('Example: <code>[gis:minimap:409c1dc9-b604-4f1e-9df9-2768d050acb4]</code>'),
          ],
        ],
      ],
    ];
  }

  /**
   * Tokens.
   *
   * @implements hook_tokens().
   */
  public function tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleableMetadata): array {
    $replacements = [];
    if ('gis' === $type) {
      foreach ($tokens as $name => $original) {
        if (preg_match('/^minimap:(?P<uuid>[^:]+)(?:\:(?P<config>.+))?$/', $name, $matches)) {
          $mapUuid = $matches['uuid'];
          $elementAttributes = new Attribute();
          $elementAttributes->addClass('hoeringsportal-hearing-gis-minimap');
          if (Uuid::isValid($mapUuid)) {
            $elementId = uniqid('gis-minimap-');
            parse_str($matches['config'] ?? '', $config);
            $elementStyle['height'] = $config['height'] ?? '500px';
            $script = sprintf('<script>window.addEventListener("load", function() { MiniMap.createMiniMap({mapDiv: "%s", minimapId: "%s"}) })</script>',
              $elementId, $mapUuid);

            $elementAttributes->setAttribute('id', $elementId);
            // Build CSS style from associative array with, e.g.
            // ['height' => '500px', 'width' => '50%']
            // -> 'height: 500px; width: 50%'.
            $style = implode(
              '; ',
              // Convert array to list of `«property»: «value»`.
              array_map(
                static fn($property, $value) => $property . ': ' . $value, array_keys($elementStyle),
                $elementStyle
              )
            );
            $elementAttributes->setAttribute('style', $style);
            // Note: We use a `span` element (rather than a `div` element) for
            // the map since we're (most times) inside a `p` element.
            $markup = sprintf('<span %s></span>', $elementAttributes);

            $replacements[$original] = new FormattableMarkup($script . $markup,
              []);
          }
          else {
            $elementAttributes->addClass('invalid-token');
            $elementAttributes->setAttribute('title', $this->t('Invalid GIS map UUID: @map_uuid', ['@map_uuid' => $mapUuid]));
            $replacements[$original] = new FormattableMarkup(
              sprintf('<span %s>%s</span>', $elementAttributes, $original),
              []
            );
          }
        }
        $bubbleableMetadata->addAttachments(['library' => ['hoeringsportal_hearing/gis-minimap']]);
      }
    }

    return $replacements;
  }

}
