<?php

namespace Drupal\content_access_simple;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\Entity\Node;

/**
 * Service to handle Content Access Simple functions.
 */
class AccessManager {

  use StringTranslationTrait;

  /**
   * The account interface.
   */
  protected AccountInterface $currentUser;

  /**
   * The entity type manager interface.
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Constructs a new AccessManager service.
   *
   * @var Drupal\Core\Session\AccountInterface $current_user
   *   The current user object.
   */
  public function __construct(
    AccountInterface $current_user,
    EntityTypeManagerInterface $entity_type_manager
    ) {
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Determine if content access is turned on per node and user has permission.
   */
  public function hasAccess(Node $node) {
    $nodeType = $node->getType();
    $contentAccessPerNode = content_access_get_settings('per_node', $nodeType);

    if (!$contentAccessPerNode) {
      return FALSE;
    }

    $allNodesAccess = $this->currentUser->hasPermission('grant content access simple');
    $ownNodeAccess = $this->currentUser->hasPermission('grant own content access simple') && ($this->currentUser->id() == $node->getOwnerId());

    return $allNodesAccess || $ownNodeAccess;
  }

  /**
   * Returns a role name from a role machine name.
   *
   * @var string $role_id
   *   The role machine name.
   *
   * @return string|NULL
   *   The role human-readable name.
   */
  private function getRoleName($role_id) {
    $roleStorage = $this->entityTypeManager->getStorage('user_role');
    $roleName = $roleStorage->load($role_id);
    if (!$roleName) {
      return NULL;
    }
    return $roleName->label();
  }

  /**
   * Adds content access form elements to the node form.
   */
  public function addAccessFormElements(array &$form, $node) {

    $defaults = [];

    foreach (_content_access_get_operations() as $op => $label) {
      $defaults[$op] = content_access_per_node_setting($op, $node);
    }

    //kint($defaults['view']);

    foreach ($defaults['view'] as $role_id) {
      kint($this->getRoleName($role_id));
    }

    $form['content_access_simple'] = [
      '#type' => 'details',
      '#title' => $this->t('Access and Permissions'),
      '#open' => FALSE,
      '#weight' => 100,
    ];

  }

}
