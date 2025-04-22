<?php

namespace Drupal\hoeringsportal_config_settings\Twig;

use Drupal\itk_admin\State\BaseConfig;
use Drupal\media\Entity\Media;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Custom Twig extensions for Høringsportal.
 */
class TwigExtension extends AbstractExtension {

  /**
   * {@inheritdoc}
   */
  public function __construct(private readonly BaseConfig $config) {

  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new TwigFunction(
        'hoeringsportal_config',
          $this->getConfig(...),
        [
          'is_safe' => ['all'],
        ]
      ),
      new TwigFunction(
        'hoeringsportal_config_media',
        $this->getConfigMedia(...),
        [
          'is_safe' => ['all'],
        ]
      ),
    ];
  }

  /**
   * Get config.
   */
  public function getConfig(string $key): ?string {
    return $this->config->get($key);
  }

  /**
   * Get config media.
   */
  public function getConfigMedia(string $key): ?Media {
    $id = $this->config->get($key);

    return Media::load($id ?? -1);
  }

}
