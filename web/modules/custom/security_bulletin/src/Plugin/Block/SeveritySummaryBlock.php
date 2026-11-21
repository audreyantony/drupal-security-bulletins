<?php

declare(strict_types=1);

namespace Drupal\security_bulletin\Plugin\Block;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\security_bulletin\Severity;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * COUNTS PUBLISHED BULLETINS BY SEVERITY OVER A CONFIGURABLE PERIOD.
 *
 * EACH COUNT LINKS TO THE BULLETIN LISTING FILTERED ON THAT SEVERITY.
 */
#[Block(
  id: 'security_bulletin_severity_summary',
  admin_label: new TranslatableMarkup('Bulletin severity summary'),
  category: new TranslatableMarkup('Security bulletins'),
)]
final class SeveritySummaryBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * CACHE TAG INVALIDATED DAILY BY CRON, SO COUNTS FOLLOW THE TIME WINDOW.
   */
  public const CACHE_TAG = 'security_bulletin:severity_summary';

  /**
   * SEVERITIES SHOWN IN THE BLOCK, MOST SEVERE FIRST.
   */
  private const DISPLAYED_SEVERITIES = [
    Severity::Critical,
    Severity::High,
    Severity::Medium,
    Severity::Low,
  ];

  /**
   * ROUTE OF THE BULLETIN LISTING PAGE.
   */
  private const LISTING_ROUTE = 'view.bulletins.page_1';

  /**
   * QUERY KEY OF THE SEVERITY FILTER EXPOSED BY THE LISTING.
   */
  private const FILTER_KEY = 'field_severity_value';

  /**
   * CONSTRUCTS THE BLOCK.
   *
   * @param array $configuration
   *   THE PLUGIN CONFIGURATION.
   * @param string $plugin_id
   *   THE PLUGIN ID.
   * @param mixed $plugin_definition
   *   THE PLUGIN DEFINITION.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   THE ENTITY TYPE MANAGER.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   *   THE ENTITY FIELD MANAGER.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   THE TIME SERVICE.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly EntityFieldManagerInterface $entityFieldManager,
    private readonly TimeInterface $time,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('datetime.time'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return ['days' => 30] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state): array {
    $form['days'] = [
      '#type' => 'number',
      '#title' => $this->t('Period in days'),
      '#description' => $this->t('Only bulletins published during this period are counted.'),
      '#min' => 1,
      '#max' => 365,
      '#required' => TRUE,
      '#default_value' => $this->configuration['days'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state): void {
    $this->configuration['days'] = (int) $form_state->getValue('days');
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $days = (int) $this->configuration['days'];
    $since = $this->time->getRequestTime() - $days * 86400;

    // LABELS COME FROM THE FIELD SETTINGS, LIKE THE BADGES ON BULLETINS.
    $storage = $this->entityFieldManager->getFieldStorageDefinitions('node')['field_severity'] ?? NULL;
    $labels = $storage?->getSetting('allowed_values') ?? [];

    $items = [];
    foreach (self::DISPLAYED_SEVERITIES as $severity) {
      $items[] = [
        'key' => $severity->value,
        'label' => $labels[$severity->value] ?? $severity->value,
        'count' => $this->countPublished($severity, $since),
        'url' => Url::fromRoute(self::LISTING_ROUTE, [], [
          'query' => [self::FILTER_KEY => $severity->value],
        ])->toString(),
      ];
    }

    return [
      '#theme' => 'security_bulletin_severity_summary',
      '#items' => $items,
      '#days' => $days,
      '#total' => array_sum(array_column($items, 'count')),
      '#cache' => [
        // BULLETIN CHANGES, DAILY CRON INVALIDATION AND SEVERITY LABEL CHANGES.
        'tags' => [
          'node_list:bulletin',
          self::CACHE_TAG,
          'config:field.storage.node.field_severity',
        ],
        // COUNTS DEPEND ON NODE ACCESS, LABELS ON THE INTERFACE LANGUAGE.
        'contexts' => ['user.node_grants:view', 'languages:language_interface'],
      ],
    ];
  }

  /**
   * COUNTS PUBLISHED BULLETINS OF ONE SEVERITY CREATED SINCE A TIMESTAMP.
   *
   * @param \Drupal\security_bulletin\Severity $severity
   *   THE SEVERITY TO COUNT.
   * @param int $since
   *   THE OLDEST CREATION TIMESTAMP TO INCLUDE.
   *
   * @return int
   *   THE NUMBER OF BULLETINS THE CURRENT USER CAN VIEW.
   */
  private function countPublished(Severity $severity, int $since): int {
    return (int) $this->entityTypeManager->getStorage('node')->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'bulletin')
      ->condition('status', NodeInterface::PUBLISHED)
      ->condition('field_severity', $severity->value)
      ->condition('created', $since, '>=')
      ->count()
      ->execute();
  }

}