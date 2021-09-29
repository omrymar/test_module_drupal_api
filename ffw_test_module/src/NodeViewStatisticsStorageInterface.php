<?php

namespace Drupal\ffw_test_module;

/**
 * Provides an interface defining Node View Statistics Storage.
 *
 * Stores the views per day, total views and timestamp of last view
 * for entities.
 */
interface NodeViewStatisticsStorageInterface {

  /**
   * Create a record that a node was viewed.
   *
   * @return bool
   */
  public function recordNodeView(string $nid, string $uid);

  /**
   * Get total today views for given nid.
   *
   * @param string $nid
   * A node id.
   *
   * @return int
   */
  public function getDayCount(string $nid);

  /**
   * Get total count of views for given nid.
   *
   * @param string $nid
   *   A node id.
   *
   * @return int
   */
  public function getTotalCount(string $nid);

  /**
   * Get last viewer information by nid.
   *
   * @param string $nid
   *   A node id.
   *
   * @return array
   *   Return associative array of user_id as a kay and a the timestamp as a value
   */
  public function getLastViewerData(string $nid);

}
