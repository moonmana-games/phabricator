<?php

final class ManiphestTaskHasBlockerRelationship
  extends ManiphestTaskRelationship {

  const RELATIONSHIPKEY = 'task.has-blocker';

  public function getEdgeConstant() {
    return ManiphestTaskDependsOnTaskEdgeType::EDGECONST;
  }

  protected function getActionName() {
    return pht('Edit Blocker Tasks');
  }

  protected function getActionIcon() {
    return 'fa-chevron-circle-down';
  }

  public function canRelateObjects($src, $dst) {
    return ($dst instanceof ManiphestTask);
  }

  public function shouldAppearInActionMenu() {
    return false;
  }

  public function getDialogTitleText() {
    return pht('Edit Blocker Tasks');
  }

  public function getDialogHeaderText() {
    return pht('Current Blocker Tasks');
  }

  public function getDialogButtonText() {
    return pht('Save Blocker Tasks');
  }

  protected function newRelationshipSource() {
    return id(new ManiphestTaskRelationshipSource())
      ->setSelectedFilter('open');
  }

}
