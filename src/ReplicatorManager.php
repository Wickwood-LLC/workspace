<?php

/**
 * @file
 * Contains \Drupal\workspace\ReplicatorManager.
 */

namespace Drupal\workspace;
use Drupal\replication\Entity\ReplicationLog;

/**
 * Provides the Replicator manager.
 */
class ReplicatorManager implements ReplicatorInterface {

  protected $replicators = [];

  /**
   * {@inheritdoc}
   */
  public function applies(WorkspacePointerInterface $source, WorkspacePointerInterface $target) {
    return TRUE;
  }

  /**
   * Adds replication services.
   *
   * @param ReplicatorInterface $replicator
   */
  public function addReplicator(ReplicatorInterface $replicator) {
    $this->replicators[] = $replicator;
  }

  /**
   * {@inheritdoc}
   */
  public function replicate(WorkspacePointerInterface $source, WorkspacePointerInterface $target) {
    /** @var ReplicatorInterface $replicator */
    foreach ($this->replicators as $replicator) {
      if ($replicator->applies($source, $target)) {
        return $replicator->replicate($source, $target);
      }
    }
    $time = new \DateTime();
    $history = [
      'start_time' => $time->format('D, d M Y H:i:s e'),
      'end_time' => $time->format('D, d M Y H:i:s e'),
      'session_id' => \md5((\microtime(true) * 1000000)),
      'start_last_seq' => $source->getWorkspace()->getUpdateSeq(),
    ];
    $replication_log_id = $source->generateReplicationId($target);
    /** @var \Drupal\replication\Entity\ReplicationLogInterface $replication_log */
    $replication_log = ReplicationLog::loadOrCreate($replication_log_id);
    $replication_log->set('ok', FALSE);
    $replication_log->setSourceLastSeq($source->getWorkspace()->getUpdateSeq());
    $replication_log->setSessionId($history['session_id']);
    $replication_log->setHistory($history);
    $replication_log->save();
    return $replication_log;
  }
}
