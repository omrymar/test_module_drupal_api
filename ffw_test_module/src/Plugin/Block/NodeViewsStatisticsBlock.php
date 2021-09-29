<?php

namespace Drupal\ffw_test_module\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\ffw_test_module\NodeViewStatisticsStorage;
use Drupal\ffw_test_module\NodeViewStatisticsStorageInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class NodeViewsStatisticsBlock
 * @package Drupal\ffw_test_module\Plugin\Block
 *
 * @Block(
 *   id = "ffw_test_module_node_views_statistics_block",
 *   admin_label = @Translation("Node views statistics"),
 *   category = @Translation("Custom"),
 * )
 */
class NodeViewsStatisticsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack $requestStack
   */
  protected $requestStack;

  /**
   * @var NodeViewStatisticsStorageInterface $nodeViewStatistics
   */
  protected $nodeViewStatistics;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * NodeViewsStatisticsBlock constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param RequestStack $request_stack
   * @param NodeViewStatisticsStorage $node_view_statistics
   * @param AccountInterface $current_user
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RequestStack $request_stack, NodeViewStatisticsStorage $node_view_statistics, AccountInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->requestStack = $request_stack->getCurrentRequest();
    $this->nodeViewStatistics = $node_view_statistics;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('request_stack'),
      $container->get('ffw_test_module.node_views_statistic_storage'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = $this->requestStack->get('node');
    if (empty($node) || (!$node instanceof NodeInterface)) {
      return [];
    }
    $node_id = $node->id();
    $last_viewer_data = $this->nodeViewStatistics->getLastViewerData($node_id);
    if (empty($last_viewer_data)) {
      $timestamp = time();
    }
    else {
      $timestamp = reset($last_viewer_data);
    }
    $user_name =
      $this->currentUser->isAuthenticated() ? $this->currentUser->getAccountName() : $this->t('anonymous user');
    $day_count = $this->nodeViewStatistics->getDayCount($node_id);
    if ($day_count == null) {
      // If data is not available in the database then set the day count to 0
      $day_count = 0;
    }
    $total_count = $this->nodeViewStatistics->getTotalCount($node_id);
    if ($total_count == null) {
      // If data is not available in the database then set the day count to 0
      $total_count = 0;
    }
    $block = [
      '#theme' => 'ffw_test_module_node_views_statistics_block',
      '#user_name' => $user_name,
      '#daycount' => $day_count,
      '#totalcount' => $total_count,
      '#timestamp' => date('m.d.y H:i', $timestamp),
    ];

    return $block;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
