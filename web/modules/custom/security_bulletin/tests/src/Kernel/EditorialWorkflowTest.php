<?php

declare(strict_types=1);

namespace Drupal\Tests\security_bulletin\Kernel;

use Drupal\node\NodeInterface;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;
use Drupal\workflows\Entity\Workflow;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;

/**
 * TESTS THE ACCESS RULES OF THE EDITORIAL WORKFLOW, AS EXPORTED BY THE SITE.
 *
 * THE WORKFLOW AND THE ROLES ARE READ FROM CONFIG/SYNC: A CHANGE MADE IN THE
 * ADMIN INTERFACE AND EXPORTED IS CHECKED HERE.
 */
#[CoversNothing]
#[Group('security_bulletin')]
final class EditorialWorkflowTest extends BulletinKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['workflows', 'content_moderation'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('content_moderation_state');
    $this->installConfig(['content_moderation']);

    Workflow::create($this->readSiteConfig('workflows.workflow.editorial'))->save();

    // KEEP ONLY THE PERMISSIONS OF MODULES INSTALLED IN THIS TEST.
    $available = array_keys($this->container->get('user.permissions')->getPermissions());
    foreach (['anonymous', 'author', 'reviewer'] as $role_id) {
      $values = $this->readSiteConfig('user.role.' . $role_id);
      $values['permissions'] = array_values(array_intersect($values['permissions'], $available));
      Role::create($values)->save();
    }

    // USER 1 BYPASSES ALL ACCESS CHECKS: CREATE IT FIRST, SO THE USERS CREATED
    // BY THE TESTS ARE ORDINARY ONES.
    $this->createUser();
  }

  /**
   * CHECKS THAT AN AUTHOR CAN SUBMIT A DRAFT BUT NOT PUBLISH IT.
   */
  public function testAuthorCannotPublish(): void {
    $author = $this->createUserWithRole('author');
    $draft = $this->createBulletin(9.8, [
      'moderation_state' => 'draft',
      'uid' => $author->id(),
    ]);

    $transitions = $this->validTransitions($draft, $author);
    $this->assertContains('submit_for_review', $transitions);
    $this->assertNotContains('publish', $transitions);
  }

  /**
   * CHECKS THAT A REVIEWER CAN PUBLISH ONLY AFTER REVIEW.
   */
  public function testReviewerPublishesOnlyReviewedBulletins(): void {
    $reviewer = $this->createUserWithRole('reviewer');

    $draft = $this->createBulletin(9.8, ['moderation_state' => 'draft']);
    $this->assertNotContains('publish', $this->validTransitions($draft, $reviewer));

    $in_review = $this->createBulletin(9.8, ['moderation_state' => 'needs_review']);
    $this->assertContains('publish', $this->validTransitions($in_review, $reviewer));
  }

  /**
   * CHECKS THAT VISITORS SEE PUBLISHED BULLETINS ONLY.
   */
  public function testAnonymousSeesOnlyPublishedBulletins(): void {
    $anonymous = User::getAnonymousUser();
    $draft = $this->createBulletin(9.8, ['moderation_state' => 'draft']);
    $published = $this->createBulletin(9.8, ['moderation_state' => 'published']);

    $this->assertFalse($draft->access('view', $anonymous));
    $this->assertTrue($published->access('view', $anonymous));
  }

  /**
   * CREATES A USER WITH ONE OF THE SITE ROLES.
   *
   * @param string $role_id
   *   THE ROLE MACHINE NAME.
   *
   * @return \Drupal\user\UserInterface
   *   THE SAVED USER.
   */
  private function createUserWithRole(string $role_id): UserInterface {
    return $this->createUser([], NULL, FALSE, ['roles' => [$role_id]]);
  }

  /**
   * LISTS THE TRANSITIONS A USER MAY APPLY TO A BULLETIN.
   *
   * @param \Drupal\node\NodeInterface $node
   *   THE BULLETIN.
   * @param \Drupal\user\UserInterface $user
   *   THE USER.
   *
   * @return string[]
   *   THE TRANSITION MACHINE NAMES.
   */
  private function validTransitions(NodeInterface $node, UserInterface $user): array {
    $validator = $this->container->get('content_moderation.state_transition_validation');
    return array_keys($validator->getValidTransitions($node, $user));
  }

}
