<?php

namespace Drupal\hoeringsportal_config_settings\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Drupal\itk_admin\State\BaseConfig;

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
    ];
  }

  /**
   * Get config
   */
  public function getConfig(string $key): ?string {
    return $this->config->get($key);
  }

}
