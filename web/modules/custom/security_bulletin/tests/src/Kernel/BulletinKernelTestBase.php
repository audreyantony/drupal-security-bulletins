<?php

declare(strict_types=1);

namespace Drupal\Tests\security_bulletin\Kernel;

use Drupal\Core\Config\FileStorage;
use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\node\NodeInterface;

/**
 * BASE CLASS FOR KERNEL TESTS THAT NEED THE BULLETIN CONTENT MODEL.
 *
 * THE FIELDS USED BY THE MODULE COME FROM THE SITE'S EXPORTED CONFIGURATION,
 * SO THE TESTS RUN AGAINST THE REAL CONTENT MODEL.
 */
abstract class BulletinKernelTestBase extends KernelTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'node',
    'field',
    'text',
    'filter',
    'options',
    'security_bulletin',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installSchema('node', ['node_access']);
    $this->installConfig(['system', 'filter', 'node']);

    NodeType::create(['type' => 'bulletin', 'name' => 'Bulletin'])->save();
    foreach (['field_cvss_score', 'field_severity'] as $field_name) {
      $this->createFromSiteConfig('field_storage_config', 'field.storage.node.' . $field_name);
      $this->createFromSiteConfig('field_config', 'field.field.node.bulletin.' . $field_name);
    }
  }

  /**
   * READS ONE CONFIGURATION OBJECT FROM THE SITE'S EXPORT DIRECTORY.
   *
   * @param string $name
   *   THE CONFIGURATION NAME, WITHOUT THE .YML EXTENSION.
   *
   * @return array
   *   THE VALUES, WITHOUT THE KEYS THAT DEPEND ON THE SOURCE SITE.
   */
  protected function readSiteConfig(string $name): array {
    $storage = new FileStorage($this->root . '/../config/sync');
    $data = $storage->read($name);
    $this->assertIsArray($data, sprintf('Missing site configuration "%s".', $name));

    // UUID, LANGUAGE AND DEPENDENCIES ARE RECOMPUTED WHEN THE ENTITY IS SAVED.
    unset($data['uuid'], $data['langcode'], $data['_core'], $data['dependencies']);
    return $data;
  }

  /**
   * CREATES A CONFIGURATION ENTITY FROM THE SITE'S EXPORT DIRECTORY.
   *
   * USES THE SAME PATH AS A CONFIGURATION IMPORT, SO VALUES STORED IN THEIR
   * EXPORTED FORMAT, LIKE THE ALLOWED VALUES OF A LIST FIELD, ARE CONVERTED.
   *
   * @param string $entity_type_id
   *   THE CONFIGURATION ENTITY TYPE.
   * @param string $name
   *   THE CONFIGURATION NAME, WITHOUT THE .YML EXTENSION.
   */
  protected function createFromSiteConfig(string $entity_type_id, string $name): void {
    $storage = $this->container->get('entity_type.manager')->getStorage($entity_type_id);
    $this->assertInstanceOf(ConfigEntityStorageInterface::class, $storage);
    $storage->createFromStorageRecord($this->readSiteConfig($name))->save();
  }

  /**
   * CREATES AND SAVES A BULLETIN.
   *
   * @param float $score
   *   THE CVSS SCORE.
   * @param array $values
   *   EXTRA FIELD VALUES, OVERRIDING THE DEFAULTS.
   *
   * @return \Drupal\node\NodeInterface
   *   THE SAVED BULLETIN.
   */
  protected function createBulletin(float $score, array $values = []): NodeInterface {
    $node = Node::create($values + [
      'type' => 'bulletin',
      'title' => 'Test bulletin',
      'field_cvss_score' => $score,
      'status' => NodeInterface::PUBLISHED,
    ]);
    $node->save();
    return $node;
  }

}
