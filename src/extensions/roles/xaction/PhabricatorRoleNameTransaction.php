<?php

final class PhabricatorRoleNameTransaction
  extends PhabricatorRoleTransactionType {

  const TRANSACTIONTYPE = 'role:name';

  public function generateOldValue($object) {
    return $object->getName();
  }

  public function applyInternalEffects($object, $value) {
    $object->setName($value);
    if (!$this->getEditor()->getIsMilestone()) {
      $object->setPrimarySlug(PhabricatorSlug::normalizeProjectSlug($value));
    }
  }

  public function applyExternalEffects($object, $value) {
    $old = $this->getOldValue();

    // First, add the old name as a secondary slug; this is helpful
    // for renames and generally a good thing to do.
    if (!$this->getEditor()->getIsMilestone()) {
      if ($old !== null) {
        $this->getEditor()->addSlug($object, $old, false);
      }
      $this->getEditor()->addSlug($object, $value, false);
    }
    return;
  }

  public function getTitle() {
    $old = $this->getOldValue();
    if ($old === null) {
      return pht(
        '%s created this role.',
        $this->renderAuthor());
    } else {
      return pht(
        '%s renamed this role from %s to %s.',
        $this->renderAuthor(),
        $this->renderOldValue(),
        $this->renderNewValue());
    }
  }

  public function getTitleForFeed() {
    $old = $this->getOldValue();
    if ($old === null) {
      return pht(
        '%s created %s.',
        $this->renderAuthor(),
        $this->renderObject());
    } else {
      return pht(
        '%s renamed %s from %s to %s.',
        $this->renderAuthor(),
        $this->renderObject(),
        $this->renderOldValue(),
        $this->renderNewValue());
    }
  }

  public function validateTransactions($object, array $xactions) {
    $errors = array();

    if ($this->isEmptyTextTransaction($object->getName(), $xactions)) {
      $errors[] = $this->newRequiredError(pht('Roles must have a name.'));
    }

    $max_length = $object->getColumnMaximumByteLength('name');
    foreach ($xactions as $xaction) {
      $new_value = $xaction->getNewValue();
      $new_length = strlen($new_value);
      if ($new_length > $max_length) {
        $errors[] = $this->newInvalidError(
          pht(
            'Role names must not be longer than %s character(s).',
            new PhutilNumber($max_length)));
      }
    }

    if ($this->getEditor()->getIsMilestone() || !$xactions) {
      return $errors;
    }

    $name = last($xactions)->getNewValue();

    if (!PhabricatorSlug::isValidProjectSlug($name)) {
      $errors[] = $this->newInvalidError(
        pht('Role names must contain at least one letter or number.'));
   }

    $slug = PhabricatorSlug::normalizeProjectSlug($name);

    $slug_used_already = id(new PhabricatorRoleSlug())
      ->loadOneWhere('slug = %s', $slug);
    if ($slug_used_already &&
        $slug_used_already->getRolePHID() != $object->getPHID()) {

      $errors[] = $this->newInvalidError(
        pht(
          'Role name generates the same hashtag ("%s") as another '.
          'existing role. Choose a unique name.',
          '#'.$slug));
    }

    return $errors;
  }

}
