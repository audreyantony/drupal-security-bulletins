<?php

declare(strict_types=1);

namespace Drupal\Tests\security_bulletin\Kernel;

use Drupal\node\NodeInterface;
use Drupal\security_bulletin\Plugin\Block\SeveritySummaryBlock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

/**
 * TESTS THE COUNTS, LINKS AND CACHE METADATA OF THE SEVERITY SUMMARY BLOCK.
 */
#[CoversClass(SeveritySummaryBlock::class)]
#[Group('security_bulletin')]
final class SeveritySummaryBlockTest extends BulletinKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['security_bulletin_test'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    // THE TEST MODULE ROUTE MUST BE IN THE ROUTER TO GENERATE TILE LINKS.
    $this->container->get('router.builder')->rebuild();
  }

  /**
   * CHECKS THAT ONLY RECENT PUBLISHED BULLETINS ARE COUNTED.
   */
  public function testCountsRecentPublishedBulletins(): void {
    $day = 86400;
    $now = $this->container->get('datetime.time')->getRequestTime();
    $this->createBulletin(9.8, ['created' => $now - $day]);
    $this->createBulletin(7.5, ['created' => $now - 5 * $day]);
    // TOO OLD FOR A 30-DAY PERIOD.
    $this->createBulletin(9.1, ['created' => $now - 40 * $day]);
    // NOT PUBLISHED.
    $this->createBulletin(9.5, [
      'created' => $now - $day,
      'status' => NodeInterface::NOT_PUBLISHED,
    ]);

    $build = $this->buildBlock(30);
    $this->assertSame(
      ['critical' => 1, 'high' => 1, 'medium' => 0, 'low' => 0],
      array_column($build['#items'], 'count', 'key'),
    );
    $this->assertSame(2, $build['#total']);
  }

  /**
   * CHECKS THAT THE PERIOD SETTING CHANGES WHAT IS COUNTED.
   */
  public function testPeriodIsConfigurable(): void {
    $now = $this->container->get('datetime.time')->getRequestTime();
    $this->createBulletin(9.8, ['created' => $now - 10 * 86400]);

    $this->assertSame(0, $this->buildBlock(7)['#total']);
    $this->assertSame(1, $this->buildBlock(30)['#total']);
  }

  /**
   * CHECKS THAT EACH TILE LINKS TO THE LISTING FILTERED ON ITS SEVERITY.
   */
  public function testTilesLinkToTheFilteredListing(): void {
    $urls = array_column($this->buildBlock(30)['#items'], 'url', 'key');
    $this->assertStringEndsWith('/bulletins?field_severity_value=critical', $urls['critical']);
    $this->assertStringEndsWith('/bulletins?field_severity_value=low', $urls['low']);
  }

  /**
   * CHECKS THE CACHE TAGS THAT KEEP THE COUNTS UP TO DATE.
   */
  public function testCacheTags(): void {
    $tags = $this->buildBlock(30)['#cache']['tags'];
    $this->assertContains('node_list:bulletin', $tags);
    $this->assertContains(SeveritySummaryBlock::CACHE_TAG, $tags);
  }

  /**
   * BUILDS THE BLOCK FOR A GIVEN PERIOD.
   *
   * @param int $days
   *   THE PERIOD IN DAYS.
   *
   * @return array
   *   THE BLOCK RENDER ARRAY.
   */
  private function buildBlock(int $days): array {
    return $this->container->get('plugin.manager.block')
      ->createInstance('security_bulletin_severity_summary', ['days' => $days])
      ->build();
  }

}
