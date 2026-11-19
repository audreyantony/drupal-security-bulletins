<?php

declare(strict_types=1);

namespace Drupal\security_bulletin;

/**
 * SEVERITY CALCULATOR BASED ON THE CVSS V3 QUALITATIVE RATING SCALE.
 */
final class CvssV3SeverityCalculator implements SeverityCalculatorInterface {

  /**
   * {@inheritdoc}
   */
  public function fromScore(float $score): Severity {
    if ($score < 0.0 || $score > 10.0) {
      throw new \InvalidArgumentException(sprintf('CVSS score must be between 0.0 and 10.0, %s given.', $score));
    }

    // THRESHOLDS: 0.0 NONE, 0.1-3.9 LOW, 4.0-6.9 MEDIUM, 7.0-8.9 HIGH, 9.0-10.0 CRITICAL.
    return match (TRUE) {
      $score === 0.0 => Severity::None,
      $score < 4.0 => Severity::Low,
      $score < 7.0 => Severity::Medium,
      $score < 9.0 => Severity::High,
      default => Severity::Critical,
    };
  }

}