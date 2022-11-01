<?php

namespace Drupal\server_general\Plugin\EntityViewBuilder;

use Drupal\intl_date\IntlDate;
use Drupal\media\MediaInterface;
use Drupal\node\Entity\NodeType;
use Drupal\node\NodeInterface;
use Drupal\server_general\EntityDateTrait;
use Drupal\server_general\EntityViewBuilder\NodeViewBuilderAbstract;
use Drupal\server_general\SocialShareTrait;
use Drupal\server_general\TitleAndLabelsTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Link;

/**
 * The "Node Group" plugin.
 *
 * @EntityViewBuilder(
 *   id = "node.group",
 *   label = @Translation("Node - Group"),
 *   description = "Node view builder for Group bundle."
 * )
 */
class NodeGroup extends NodeViewBuilderAbstract {

  use EntityDateTrait;
  use SocialShareTrait;
  use TitleAndLabelsTrait;

  /**
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * @param \Drupal\Core\Session\AccountInterface $current_user
   */
  public function __construct(AccountInterface $account) {
    $this->account = $account;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    // Instantiates this form class.
    return new static(
      // Load the service required to construct this class.
      $container->get('current_user')
    );
  }

  /**
   * Build full view mode.
   *
   * @param array $build
   *   The existing build.
   * @param \Drupal\node\NodeInterface $entity
   *   The entity.
   *
   * @return array
   *   Render array.
   */
  public function buildFull(array $build, NodeInterface $entity) {
    $elements = [];
    // Show a group subscribe message to user if they are logged in.
    if ($this->account->isAuthenticated()) {
      // Set the subscribe URL.
      $url = Link::createFromRoute(t('click here'), 'og.subscribe', ['entity_type_id' => 'node', 'group' => $entity->id()]);
      $membershipUrl = $url->toString();
      $this->messenger()->addMessage(t('Hi %name, %url if you would like to subscribe to this group called %label',
      [
        '%name' => $this->account->getDisplayName(),
        '%url' => $membershipUrl,
        '%label' => $entity->label(),
      ]));
    }
    // Header.
    $element = $this->buildHeader($entity);
    $elements[] = $this->wrapContainerWide($element);

    $elements = $this->wrapContainerVerticalSpacing($elements);
    $build[] = $this->wrapContainerBottomPadding($elements);

    return $build;
  }

  /**
   * Build the header.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The entity.
   *
   * @return array
   *   Render array
   *
   * @throws \IntlException
   */
  protected function buildHeader(NodeInterface $entity): array {
    $elements = [];

    $elements[] = $this->buildConditionalPageTitle($entity);

    // Show the node type as a label.
    $node_type = NodeType::load($entity->bundle());
    $elements[] = $this->buildLabelsFromText([$node_type->label()]);

    $elements = $this->wrapContainerVerticalSpacing($elements);
    return $this->wrapContainerNarrow($elements);
  }

}
