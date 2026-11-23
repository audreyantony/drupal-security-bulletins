<?php

declare(strict_types=1);

namespace Drupal\Tests\security_bulletin\Kernel;

use Drupal\Core\Cache\Cache;
use Drupal\security_bulletin\Hook\CacheHooks;
use Drupal\security_bulletin\Plugin\Block\SeveritySummaryBlock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

/**
 * TESTS THE DAILY INVALIDATION OF THE SEVERITY SUMMARY CACHE BY CRON.
 */
#[CoversClass(CacheHooks::class)]
#[Group('security_bulletin')]
final class CacheHooksTest extends BulletinKernelTestBase {

  /**
   * CACHE ID OF THE ITEM USED TO OBSERVE INVALIDATIONS.
   */
  private const CID = 'security_bulletin_test_item';

  /**
   * CHECKS ONE INVALIDATION PER DAY, NOT ONE PER CRON RUN.
   */
  public function testInvalidatesOncePerDay(): void {
    $cache = $this->container->get('cache.default');
    $tags = [SeveritySummaryBlock::CACHE_TAG];

    $cache->set(self::CID, 'counts', Cache::PERMANENT, $tags);
    $this->runCron();
    $this->assertFalse($cache->get(self::CID), 'The first cron run invalidates the summary.');

    $cache->set(self::CID, 'counts', Cache::PERMANENT, $tags);
    $this->runCron();
    $this->assertNotFalse($cache->get(self::CID), 'A second run on the same day keeps it.');

    // PRETEND THE LAST INVALIDATION HAPPENED A DAY AGO.
    $now = $this->container->get('datetime.time')->getRequestTime();
    $this->container->get('state')->set('security_bulletin.severity_summary_invalidated', $now - 86400);
    $this->runCron();
    $this->assertFalse($cache->get(self::CID), 'The next day, the summary is invalidated again.');
  }

  /**
   * RUNS THE CRON HOOK OF THE MODULE ONLY.
   */
  private function runCron(): void {
    $this->container->get('module_handler')->invoke('security_bulletin', 'cron');
  }

}
