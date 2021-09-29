<?php

namespace Drupal\ffw_test_module;

use Drupal\Core\Database\Connection;
use Drupal\Core\State\StateInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class NodeViewStatisticsStorage
 *
 * @package Drupal\ffw_test_module
 */
class NodeViewStatisticsStorage implements NodeViewStatisticsStorageInterface {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $dbConnection;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The name of the table used to store node views statistics.
   *
   * @var string
   */
  protected $tableName;

  /**
   * Constructor for the NodeViewStatisticsStorage service.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(Connection $connection, RequestStack $request_stack) {
    $this->dbConnection = $connection;
    $this->requestStack = $request_stack;
    //
    $this->tableName = 'ffw_test_node_statistics';
  }

  /**
   * {@inheritdoc}
   */
  public function recordNodeView(string $nid, string $uid) {
    return (bool) $this->dbConnection->merge($this->tableName)
      ->keys([
        'uid' => $uid,
        'nid' => $nid,
      ])
      ->fields([
        'daycount' => 1,
        'totalcount' => 1,
        'timestamp' => $this->getRequestTime(),
      ])
      ->expression('daycount', 'daycount + 1')
      ->expression('totalcount', 'totalcount + 1')
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function getDayCount(string $nid) {
    $query = $this->dbConnection->select($this->tableName, 'node_statistics')
      ->condition('nid', $nid, '=')
      ->where('DATE(FROM_UNIXTIME(node_statistics.timestamp)) >= CURDATE()');
    $query->addExpression('SUM(daycount)');

    return $query->execute()->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function getTotalCount(string $nid) {
    $query = $this->dbConnection->select($this->tableName, 'node_statistics')
      ->condition('nid', $nid, '=');
    $query->addExpression('MAX(totalcount)');

    return (int) $query->execute()->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function getLastViewerData(string $nid) {
    $query = $this->dbConnection->select($this->tableName, 'node_statistics')
      ->fields('node_statistics', ['uid', 'timestamp'])
      ->condition('nid', $nid, '=')
      ->orderBy('node_statistics.timestamp', 'DESC')
      ->range(0, 1);

    return $query->execute()->fetchAllKeyed();
  }

  /**
   * Get current request time.
   *
   * @return int
   *   Unix timestamp for current server request time.
   */
  protected function getRequestTime() {
    return $this->requestStack->getCurrentRequest()->server->get('REQUEST_TIME');
  }

}
