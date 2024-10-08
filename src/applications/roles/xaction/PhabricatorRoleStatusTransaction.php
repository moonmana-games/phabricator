<?php

final class PhabricatorRoleStatusTransaction
  extends PhabricatorRoleTransactionType {

  const TRANSACTIONTYPE = 'role:status';

  public function generateOldValue($object) {
    return $object->getStatus();
  }

  public function applyInternalEffects($object, $value) {
    $object->setStatus($value);
  }

  public function getTitle() {
    $old = $this->getOldValue();

    if ($old == 0) {
      return pht(
        '%s archived this role.',
        $this->renderAuthor());
    } else {
      return pht(
        '%s activated this role.',
        $this->renderAuthor());
    }
  }

  public function getTitleForFeed() {
    $old = $this->getOldValue();

    if ($old == 0) {
      return pht(
        '%s archived %s.',
        $this->renderAuthor(),
        $this->renderObject());
    } else {
      return pht(
        '%s activated %s.',
        $this->renderAuthor(),
        $this->renderObject());
    }
  }

  public function getColor() {
    $old = $this->getOldValue();

    if ($old == 0) {
      return 'red';
    } else {
      return 'green';
    }
  }

  public function getIcon() {
    $old = $this->getOldValue();

    if ($old == 0) {
      return 'fa-ban';
    } else {
      return 'fa-check';
    }
  }

}
