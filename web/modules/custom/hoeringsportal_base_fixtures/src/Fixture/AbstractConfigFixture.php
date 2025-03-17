<?php

namespace Drupal\hoeringsportal_base_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Abstract config fixture.
 *
 * @package Drupal\hoeringsportal_base_fixtures\Fixture
 */
abstract class AbstractConfigFixture extends AbstractFixture {
  /**
   * The config.
   */
  protected static array $config;

  /**
   * Constructor.
   */
  public function __construct(
    protected readonly ConfigFactoryInterface $configFactory,
  ) {
    if (!isset(static::$config)) {
      throw new \RuntimeException(sprintf('Config not defined in %s',
        static::class));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function load() {
    foreach (static::$config as $name => $value) {
      $config = $this->configFactory->getEditable($name);
      foreach ($value as $key => $val) {
        $config->set($key, $val);
      }
      $config->save();
    }
  }

}
