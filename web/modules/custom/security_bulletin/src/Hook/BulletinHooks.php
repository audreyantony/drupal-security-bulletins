<?php

declare(strict_types=1);

namespace Drupal\security_bulletin\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\node\NodeInterface;
use Drupal\security_bulletin\SeverityCalculatorInterface;

/**
 * HOOK IMPLEMENTATIONS FOR SECURITY BULLETINS.
 */
final class BulletinHooks {

  /**
   * CONSTRUCTS THE HOOK CLASS.
   *
   * @param \Drupal\security_bulletin\SeverityCalculatorInterface $severityCalculator
   *   THE SEVERITY CALCULATOR.
   */
  public function __construct(
    private readonly SeverityCalculatorInterface $severityCalculator,
  ) {}

  /**
   * Implements hook_ENTITY_TYPE_presave() for node entities.
   *
   * KEEPS THE STORED SEVERITY IN SYNC WITH THE CVSS SCORE, SO VIEWS CAN FILTER ON IT.
   */
  #[Hook('node_presave')]
  public function nodePresave(NodeInterface $node): void {
    if ($node->bundle() !== 'bulletin'
      || !$node->hasField('field_severity')
      || !$node->hasField('field_cvss_score')) {
      return;
    }

    $score = $node->get('field_cvss_score');
    if ($score->isEmpty()) {
      $node->set('field_severity', NULL);
      return;
    }

    $severity = $this->severityCalculator->fromScore((float) $score->value);
    $node->set('field_severity', $severity->value);
  }

}