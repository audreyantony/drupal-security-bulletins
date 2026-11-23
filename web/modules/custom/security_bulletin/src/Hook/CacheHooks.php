<?php

declare(strict_types=1);

namespace Drupal\security_bulletin\Hook;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\State\StateInterface;
use Drupal\security_bulletin\Plugin\Block\SeveritySummaryBlock;

/**
 * CACHE MAINTENANCE FOR SECURITY BULLETINS.
 */
final class CacheHooks {

  /**
   * STATE KEY STORING THE TIME OF THE LAST SUMMARY INVALIDATION.
   */
  private const STATE_KEY = 'security_bulletin.severity_summary_invalidated';

  /**
   * CONSTRUCTS THE HOOK CLASS.
   *
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cacheTagsInvalidator
   *   THE CACHE TAGS INVALIDATOR.
   * @param \Drupal\Core\State\StateInterface $state
   *   THE STATE SERVICE.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   THE TIME SERVICE.
   */
  public function __construct(
    private readonly CacheTagsInvalidatorInterface $cacheTagsInvalidator,
    private readonly StateInterface $state,
    private readonly TimeInterface $time,
  ) {}

  /**
   * Implements hook_cron().
   *
   * ONCE A DAY, CLEARS EVERY CACHED PAGE THAT SHOWS THE SEVERITY SUMMARY, SO
   * BULLETINS OLDER THAN THE PERIOD STOP BEING COUNTED EVEN WITHOUT EDITS.
   */
  #[Hook('cron')]
  public function cron(): void {
    $now = $this->time->getRequestTime();
    $last = (int) $this->state->get(self::STATE_KEY, 0);
    if ($now - $last < 86400) {
      return;
    }
    $this->cacheTagsInvalidator->invalidateTags([SeveritySummaryBlock::CACHE_TAG]);
    $this->state->set(self::STATE_KEY, $now);
  }

}
