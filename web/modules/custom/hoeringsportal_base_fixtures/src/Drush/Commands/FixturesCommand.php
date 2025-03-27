<?php

namespace Drupal\hoeringsportal_base_fixtures\Drush\Commands;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drush\Attributes as CLI;
use Drush\Commands\AutowireTrait;
use Drush\Commands\DrushCommands;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\VarExporter\VarExporter;

/**
 * A Drush commandfile.
 */
final class FixturesCommand extends DrushCommands {

  use AutowireTrait;

  /**
   * Constructs a FixturesCommand object.
   */
  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {
    parent::__construct();
  }

  /**
   * Dump fixture.
   */
  #[CLI\Command(name: 'hoeringsportal_base_fixtures:dump-fixture')]
  #[CLI\Argument(name: 'entityType', description: 'Entity type.')]
  #[CLI\Argument(name: 'entityId', description: 'Entity ID.')]
  #[CLI\Usage(name: 'hoeringsportal_base_fixtures:dump-fixture node 87', description: 'Dump fixture for node 87')]
  #[CLI\Usage(name: 'hoeringsportal_base_fixtures:dump-fixture media 42', description: 'Dump fixture for media 42')]
  #[CLI\Usage(name: 'hoeringsportal_base_fixtures:dump-fixture paragraph 7', description: 'Dump fixture for paragraph 7')]
  public function dump(string $entityType, string $entityId, array $options = []) {
    $entity = $this->entityTypeManager->getStorage($entityType)->load($entityId);
    if (!$entity) {
      throw new InvalidArgumentException(sprintf('Cannot find %s entity with id %s.', $entityType, $entityId));
    }
    if (!$entity instanceof ContentEntityBase) {
      throw new InvalidArgumentException(sprintf('Entity must be an instance of %s.', ContentEntityBase::class));
    }

    $this->io()->warning(<<<'EOF'
The code below is only a scaffolding and it may not work.

Caveats: Only fields whose names start with `field_` are handled.
EOF
    );

    $this->output()->writeln([
      '``` php',
      sprintf('%s::create(%s)', $entity::class, VarExporter::export($this->convertEntity($entity))),
      '```',
    ]);
    $this->output()->writeln('');
  }

  /**
   * Convert entity to array.
   *
   * @return array
   *   An array suitable for passing to
   *   Drupal\Core\Entity\EntityInterface::create().
   */
  private function convertEntity(ContentEntityBase $entity): array {
    $data = [
      'type' => $entity->bundle(),
    ];

    foreach ($entity->getFields(FALSE) as $field) {
      if (str_starts_with($field->getName(), 'field_')) {
        $value = $field->getValue();
        // Unwrap singleton array value containing an array.
        if (array_is_list($value) && 1 === count($value)) {
          $item = reset($value);
          if (is_array($item)) {
            $value = $item;
          }
        }
        if (is_array($value) && 1 === count($value) && isset($value['value'])) {
          $value = $value['value'];
        }
        $data[$field->getName()] = $value;
      }
    }

    return $data;
  }

}
