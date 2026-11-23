<?php

declare(strict_types=1);

namespace Drupal\security_bulletin\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Attribute\FieldFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\security_bulletin\SeverityCalculatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * DISPLAYS A CVSS SCORE WITH ITS SEVERITY AND A GAUGE.
 */
#[FieldFormatter(
  id: 'cvss_badge',
  label: new TranslatableMarkup('CVSS badge'),
  field_types: ['decimal', 'float'],
)]
final class CvssBadgeFormatter extends FormatterBase {

  /**
   * THE SEVERITY CALCULATOR, SET IN CREATE().
   */
  protected SeverityCalculatorInterface $severityCalculator;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    // KEEP THE PARENT CONSTRUCTOR UNTOUCHED AND ADD OUR DEPENDENCY AFTERWARDS.
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->severityCalculator = $container->get('security_bulletin.severity_calculator');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings(): array {
    return ['size' => 'default'] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state): array {
    $element = parent::settingsForm($form, $form_state);
    $element['size'] = [
      '#type' => 'select',
      '#title' => $this->t('Size'),
      '#options' => [
        'default' => $this->t('Default'),
        'large' => $this->t('Large'),
      ],
      '#default_value' => $this->getSetting('size'),
    ];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary(): array {
    return [$this->t('Size: @size', ['@size' => $this->getSetting('size')])];
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode): array {
    $elements = [];
    foreach ($items as $delta => $item) {
      if ($item->isEmpty()) {
        continue;
      }
      $score = (float) $item->get('value')->getValue();
      $elements[$delta] = [
        '#theme' => 'security_bulletin_cvss_badge',
        '#score' => number_format($score, 1, '.', ''),
        '#percent' => (int) round($score * 10),
        // COMPUTED FROM THE SCORE,
        // SO THE FORMATTER WORKS EVEN WITHOUT A SEVERITY FIELD.
        '#severity' => $this->severityCalculator->fromScore($score)->value,
        '#size' => $this->getSetting('size'),
      ];
    }
    return $elements;
  }

}
