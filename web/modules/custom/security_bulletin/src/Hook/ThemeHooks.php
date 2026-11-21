<?php

declare(strict_types=1);

namespace Drupal\security_bulletin\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * THEME HOOK DECLARATIONS FOR SECURITY BULLETINS.
 */
final class ThemeHooks {

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme(): array {
    return [
      'security_bulletin_cvss_badge' => [
        'variables' => [
          'score' => NULL,
          'percent' => 0,
          'severity' => NULL,
          'size' => 'default',
        ],
      ],
      'security_bulletin_severity_summary' => [
        'variables' => [
          'items' => [],
          'days' => 30,
          'total' => 0,
        ],
      ],
    ];
  }

}