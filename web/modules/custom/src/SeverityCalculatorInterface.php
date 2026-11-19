<?php

declare(strict_types=1);

namespace Drupal\security_bulletin;

/**
 * CONVERTS A CVSS SCORE INTO A SEVERITY LEVEL.
 */
interface SeverityCalculatorInterface {

  /**
   * RETURNS THE SEVERITY MATCHING A CVSS SCORE.
   * @param float $score
   *   THE CVSS SCORE, FROM 0.0 TO 10.0.
   * @return \Drupal\security_bulletin\Severity
   *   THE MATCHING SEVERITY LEVEL.
   * @throws \InvalidArgumentException
   *   WHEN THE SCORE IS OUTSIDE THE 0.0 TO 10.0 RANGE.
   */
  public function fromScore(float $score): Severity;

}