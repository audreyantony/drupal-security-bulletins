<?php

declare(strict_types=1);

namespace Drupal\Tests\security_bulletin\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\security_bulletin\CvssV3SeverityCalculator;
use Drupal\security_bulletin\Severity;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * TESTS THE CVSS V3 SEVERITY THRESHOLDS.
 */
#[CoversClass(CvssV3SeverityCalculator::class)]
#[Group('security_bulletin')]
final class CvssV3SeverityCalculatorTest extends UnitTestCase {

  /**
   * THE CALCULATOR UNDER TEST.
   */
  private CvssV3SeverityCalculator $calculator;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->calculator = new CvssV3SeverityCalculator();
  }

  /**
   * CHECKS THAT EACH SCORE MAPS TO THE EXPECTED SEVERITY.
   */
  #[DataProvider('providerScores')]
  public function testFromScore(float $score, Severity $expected): void {
    $this->assertSame($expected, $this->calculator->fromScore($score));
  }

  /**
   * SCORES ON BOTH SIDES OF EVERY THRESHOLD.
   *
   * @return array<string, array{float, \Drupal\security_bulletin\Severity}>
   *   TEST CASES KEYED BY A READABLE NAME.
   */
  public static function providerScores(): array {
    return [
      'zero is none' => [0.0, Severity::None],
      'lowest non-zero score is low' => [0.1, Severity::Low],
      'top of low' => [3.9, Severity::Low],
      'bottom of medium' => [4.0, Severity::Medium],
      'top of medium' => [6.9, Severity::Medium],
      'bottom of high' => [7.0, Severity::High],
      'top of high' => [8.9, Severity::High],
      'bottom of critical' => [9.0, Severity::Critical],
      'maximum is critical' => [10.0, Severity::Critical],
    ];
  }

  /**
   * CHECKS THAT SCORES OUTSIDE THE CVSS RANGE ARE REJECTED.
   */
  #[DataProvider('providerOutOfRange')]
  public function testFromScoreRejectsOutOfRange(float $score): void {
    $this->expectException(\InvalidArgumentException::class);
    $this->calculator->fromScore($score);
  }

  /**
   * SCORES JUST OUTSIDE THE 0.0 TO 10.0 RANGE.
   *
   * @return array<string, array{float}>
   *   TEST CASES KEYED BY A READABLE NAME.
   */
  public static function providerOutOfRange(): array {
    return [
      'below zero' => [-0.1],
      'above ten' => [10.1],
    ];
  }

}
