<?php

declare(strict_types=1);

namespace Drupal\Tests\security_bulletin\Kernel;

use Drupal\node\Entity\Node;
use Drupal\security_bulletin\Hook\BulletinHooks;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

/**
 * TESTS THAT SAVING A BULLETIN STORES THE SEVERITY MATCHING ITS SCORE.
 */
#[CoversClass(BulletinHooks::class)]
#[Group('security_bulletin')]
final class BulletinHooksTest extends BulletinKernelTestBase {

  /**
   * CHECKS THE SEVERITY ON CREATION AND AFTER A SCORE CHANGE.
   */
  public function testSeverityFollowsTheScore(): void {
    $node = $this->createBulletin(9.8);
    $this->assertSame('critical', $this->reload($node->id())->get('field_severity')->value);

    $node->set('field_cvss_score', 3.5)->save();
    $this->assertSame('low', $this->reload($node->id())->get('field_severity')->value);
  }

  /**
   * CHECKS THAT REMOVING THE SCORE ALSO REMOVES THE SEVERITY.
   */
  public function testSeverityIsClearedWithoutScore(): void {
    $node = $this->createBulletin(5.0);
    $node->set('field_cvss_score', NULL)->save();
    $this->assertTrue($this->reload($node->id())->get('field_severity')->isEmpty());
  }

  /**
   * LOADS A NODE FROM THE DATABASE, BYPASSING THE STATIC CACHE.
   *
   * @param int|string $id
   *   THE NODE ID.
   *
   * @return \Drupal\node\Entity\Node
   *   THE STORED NODE.
   */
  private function reload(int|string $id): Node {
    $this->container->get('entity_type.manager')->getStorage('node')->resetCache([$id]);
    $node = Node::load($id);
    $this->assertInstanceOf(Node::class, $node);
    return $node;
  }

}
