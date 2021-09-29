<?php

namespace Drupal\ffw_test_module\Controller;

use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\ffw_test_module\NodeViewStatisticsStorageInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class NodeViewStatisticController
 *
 * @package Drupal\ffw_test_module\Controller
 */
class NodeViewStatisticController extends ControllerBase {

  /**
   * The statistic storage.
   *
   * @var \Drupal\ffw_test_module\NodeViewStatisticsStorageInterface
   */
  protected $statisticsStorage;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * NodeViewStatisticController Constructor.
   *
   * @param \Drupal\ffw_test_module\NodeViewStatisticsStorageInterface $statistic_storage
   *   The node view statistic storage service.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(
    NodeViewStatisticsStorageInterface $statistic_storage,
    AccountInterface $current_user
  ) {
    $this->statisticsStorage = $statistic_storage;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ffw_test_module.node_views_statistic_storage'),
      $container->get('current_user')
    );
  }

  /**
   * Add record that the node was viewed by current user.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @param \Drupal\node\NodeInterface $node
   *
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function viewNodeEntity(Request $request, NodeInterface $node) {
    $this->statisticsStorage->recordNodeView($node->id(), $this->currentUser->id());

    return new Response();
  }

}
