<?php

final class ManiphestBlockerGraph
  extends ManiphestTaskGraph {

  const PARENT_MARKER_TYPE = 'Direct Blocked';
  const SUBTASK_MARKER_TYPE = 'Direct Blocker';

  protected function getEdgeTypes() {
    return array(
      HasBlockedTaskEdgeType::EDGECONST,
      HasBlockerTaskEdgeType::EDGECONST,
    );
  }

  protected function getParentEdgeType() {
    return HasBlockerTaskEdgeType::EDGECONST;
  }

  protected function isParentTask($task_phid) {
    $map = $this->getSeedMap(HasBlockedTaskEdgeType::EDGECONST);
    return isset($map[$task_phid]);
  }

  protected function isChildTask($task_phid) {
    $map = $this->getSeedMap(HasBlockerTaskEdgeType::EDGECONST);
    return isset($map[$task_phid]);
  }


}
