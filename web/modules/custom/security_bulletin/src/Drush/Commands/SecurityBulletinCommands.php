<?php

declare(strict_types=1);

namespace Drupal\security_bulletin\Drush\Commands;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\security_bulletin\Demo\DemoData;
use Drush\Attributes as CLI;
use Drush\Commands\AutowireTrait;
use Drush\Commands\DrushCommands;
use Drush\Exceptions\UserAbortException;

/**
 * DRUSH COMMANDS FOR SECURITY BULLETINS.
 */
final class SecurityBulletinCommands extends DrushCommands {

  use AutowireTrait;

  /**
   * MENU RECEIVING THE LINKS TO THE DEMO PAGES.
   */
  private const FOOTER_MENU = 'footer';

  /**
   * CONSTRUCTS THE COMMAND FILE.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   THE ENTITY TYPE MANAGER.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   THE TIME SERVICE.
   */
  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly TimeInterface $time,
  ) {
    parent::__construct();
  }

  /**
   * CREATES THE FICTIONAL DEMO CONTENT.
   *
   * RUNNING IT AGAIN IS SAFE:
   * ITEMS WHOSE TITLE ALREADY EXISTS ARE KEPT AS THEY ARE.
   */
  #[CLI\Command(name: 'security-bulletin:demo-content', aliases: ['sb-demo'])]
  #[CLI\Usage(name: 'drush security-bulletin:demo-content', description: 'Asks for confirmation, then creates products, bulletins, pages and footer links.')]
  public function demoContent(): void {
    if (!$this->io()->confirm(dt('Create the fictional demo content on this site?'))) {
      throw new UserAbortException();
    }

    $products = $this->ensureTerms('products', DemoData::PRODUCTS);
    $types = $this->ensureTerms('vulnerability_types', DemoData::VULNERABILITY_TYPES);
    $now = $this->time->getRequestTime();
    $created = 0;

    foreach (DemoData::BULLETINS as $bulletin) {
      [, $is_new] = $this->ensureNode('bulletin', $bulletin['title'], [
        'created' => $now - $bulletin['age'] * 86400,
        'field_summary' => $bulletin['summary'],
        'field_description' => [
          'value' => $this->buildDescription($bulletin),
          'format' => 'basic_html',
        ],
        'field_cve_id' => $bulletin['cve'],
        'field_cvss_score' => $bulletin['score'],
        'field_products' => [['target_id' => $products[$bulletin['product']]]],
        'field_vuln_type' => [['target_id' => $types[$bulletin['type']]]],
        'field_fix_available' => $bulletin['fix'],
        'field_references' => [['uri' => 'https://example.com/advisories/' . $bulletin['reference']]],
        // BULLETINS FOLLOW THE EDITORIAL WORKFLOW: PUBLISH THEM EXPLICITLY.
        'moderation_state' => 'published',
      ]);
      $created += (int) $is_new;
    }

    foreach (DemoData::PAGES as $weight => $page) {
      [$node, $is_new] = $this->ensureNode('page', $page['title'], [
        'field_content' => ['value' => $page['body'], 'format' => 'basic_html'],
        // FIXED ALIAS: NO PATHAUTO PATTERN EXISTS FOR BASIC PAGES.
        'path' => ['alias' => $page['alias'], 'pathauto' => 0],
        'status' => NodeInterface::PUBLISHED,
      ]);
      $created += (int) $is_new;
      $this->ensureFooterLink($page['title'], (int) $node->id(), $weight);
    }

    $this->io()->success(dt('@count items created. Existing items were kept.', ['@count' => $created]));
  }

  /**
   * RETURNS TERM IDS KEYED BY NAME, CREATING THE MISSING TERMS.
   *
   * @param string $vocabulary
   *   THE VOCABULARY MACHINE NAME.
   * @param string[] $names
   *   THE TERM NAMES.
   *
   * @return int[]
   *   THE TERM IDS, KEYED BY NAME.
   */
  private function ensureTerms(string $vocabulary, array $names): array {
    $storage = $this->entityTypeManager->getStorage('taxonomy_term');
    $ids = [];
    foreach ($names as $name) {
      $existing = $storage->loadByProperties(['vid' => $vocabulary, 'name' => $name]);
      $term = $existing ? reset($existing) : $storage->create(['vid' => $vocabulary, 'name' => $name]);
      if ($term->isNew()) {
        $term->save();
      }
      $ids[$name] = (int) $term->id();
    }
    return $ids;
  }

  /**
   * LOADS A NODE BY TYPE AND TITLE, OR CREATES IT WITH THE GIVEN VALUES.
   *
   * @param string $type
   *   THE CONTENT TYPE.
   * @param string $title
   *   THE TITLE USED TO DETECT AN EXISTING NODE.
   * @param array $values
   *   THE FIELD VALUES OF A NEW NODE.
   *
   * @return array
   *   THE NODE, AND TRUE WHEN IT WAS CREATED BY THIS CALL.
   */
  private function ensureNode(string $type, string $title, array $values): array {
    // FAIL LOUDLY: DRUPAL WOULD OTHERWISE SAVE A NODE WITH AN UNKNOWN TYPE.
    if (!$this->entityTypeManager->getStorage('node_type')->load($type)) {
      throw new \RuntimeException(sprintf('Content type "%s" does not exist.', $type));
    }
    $storage = $this->entityTypeManager->getStorage('node');
    $existing = $storage->loadByProperties(['type' => $type, 'title' => $title]);
    if ($existing) {
      return [reset($existing), FALSE];
    }
    $node = $storage->create(['type' => $type, 'title' => $title, 'uid' => 1] + $values);
    $node->save();
    return [$node, TRUE];
  }

  /**
   * ADDS A FOOTER MENU LINK TO A NODE.
   *
   * NOTHING IS ADDED WHEN A LINK WITH THE SAME TITLE EXISTS.
   *
   * @param string $title
   *   THE LINK TITLE.
   * @param int $nid
   *   THE TARGET NODE ID.
   * @param int $weight
   *   THE POSITION OF THE LINK IN THE MENU.
   */
  private function ensureFooterLink(string $title, int $nid, int $weight): void {
    $storage = $this->entityTypeManager->getStorage('menu_link_content');
    if ($storage->loadByProperties(['menu_name' => self::FOOTER_MENU, 'title' => $title])) {
      return;
    }
    $storage->create([
      'title' => $title,
      'link' => ['uri' => 'entity:node/' . $nid],
      'menu_name' => self::FOOTER_MENU,
      'weight' => $weight,
    ])->save();
  }

  /**
   * BUILDS THE DESCRIPTION HTML OF A BULLETIN.
   *
   * ONE HEADING PER SECTION, LIKE HAND-WRITTEN BULLETINS.
   *
   * @param array $bulletin
   *   ONE ENTRY OF DEMODATA::BULLETINS.
   *
   * @return string
   *   THE DESCRIPTION, WITH ESCAPED TEXT.
   */
  private function buildDescription(array $bulletin): string {
    return sprintf(
      '<h2>Impact</h2><p>%s</p><h2>Versions affectées</h2><p>%s</p><h2>Remédiation</h2><p>%s</p>',
      Html::escape($bulletin['impact']),
      Html::escape($bulletin['versions']),
      Html::escape($bulletin['remediation']),
    );
  }

}
