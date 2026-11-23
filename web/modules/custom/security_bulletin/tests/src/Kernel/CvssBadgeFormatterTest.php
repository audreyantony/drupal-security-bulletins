<?php

declare(strict_types=1);

namespace Drupal\Tests\security_bulletin\Kernel;

use Drupal\security_bulletin\Plugin\Field\FieldFormatter\CvssBadgeFormatter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

/**
 * TESTS THE MARKUP PRODUCED BY THE CVSS BADGE FORMATTER.
 */
#[CoversClass(CvssBadgeFormatter::class)]
#[Group('security_bulletin')]
final class CvssBadgeFormatterTest extends BulletinKernelTestBase {

  /**
   * CHECKS THE SCORE, THE SEVERITY MODIFIER AND THE LARGE SIZE.
   */
  public function testLargeBadge(): void {
    $html = $this->renderScore(9.8, ['size' => 'large']);
    $this->assertStringContainsString('9.8 / 10', $html);
    $this->assertStringContainsString('severity--critical', $html);
    $this->assertStringContainsString('cvss-score--large', $html);
  }

  /**
   * CHECKS THE DEFAULT SIZE AND A ONE-DECIMAL SCORE.
   */
  public function testDefaultBadge(): void {
    $html = $this->renderScore(4.0, []);
    $this->assertStringContainsString('4.0 / 10', $html);
    $this->assertStringContainsString('severity--medium', $html);
    $this->assertStringNotContainsString('cvss-score--large', $html);
  }

  /**
   * RENDERS THE SCORE FIELD OF A NEW BULLETIN WITH THE CVSS BADGE FORMATTER.
   *
   * @param float $score
   *   THE CVSS SCORE.
   * @param array $settings
   *   THE FORMATTER SETTINGS.
   *
   * @return string
   *   THE RENDERED HTML.
   */
  private function renderScore(float $score, array $settings): string {
    $build = $this->createBulletin($score)->get('field_cvss_score')->view([
      'type' => 'cvss_badge',
      'label' => 'hidden',
      'settings' => $settings,
    ]);
    return (string) $this->render($build);
  }

}
