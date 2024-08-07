<?php

final class PhabricatorRoleTriggerAddRolesRule
  extends PhabricatorRoleTriggerRule {

  const TRIGGERTYPE = 'task.roles.add';

  public function getSelectControlName() {
    return pht('Add role tags');
  }

  protected function getValueForEditorField() {
    return $this->getDatasource()->getWireTokens($this->getValue());
  }

  protected function assertValidRuleRecordFormat($value) {
    if (!is_array($value)) {
      throw new Exception(
        pht(
          'Add role rule value should be a list, but is not '.
          '(value is "%s").',
          phutil_describe_type($value)));
    }
  }

  protected function assertValidRuleRecordValue($value) {
    if (!$value) {
      throw new Exception(
        pht(
          'You must select at least one role tag to add.'));
    }
  }

  protected function newDropTransactions($object, $value) {
    $role_edge_type = PhabricatorRoleObjectHasRoleEdgeType::EDGECONST;

    $xaction = $object->getApplicationTransactionTemplate()
      ->setTransactionType(PhabricatorTransactions::TYPE_EDGE)
      ->setMetadataValue('edge:type', $role_edge_type)
      ->setNewValue(
        array(
          '+' => array_fuse($value),
        ));

    return array($xaction);
  }

  protected function newDropEffects($value) {
    return array(
      $this->newEffect()
        ->setIcon('fa-briefcase')
        ->setContent($this->getRuleViewDescription($value)),
    );
  }

  protected function getDefaultValue() {
    return null;
  }

  protected function getPHUIXControlType() {
    return 'tokenizer';
  }

  private function getDatasource() {
    return id(new PhabricatorRoleDatasource())
      ->setViewer($this->getViewer());
  }

  protected function getPHUIXControlSpecification() {
    $template = id(new AphrontTokenizerTemplateView())
      ->setViewer($this->getViewer());

    $template_markup = $template->render();
    $datasource = $this->getDatasource();

    return array(
      'markup' => (string)hsprintf('%s', $template_markup),
      'config' => array(
        'src' => $datasource->getDatasourceURI(),
        'browseURI' => $datasource->getBrowseURI(),
        'placeholder' => $datasource->getPlaceholderText(),
        'limit' => $datasource->getLimit(),
      ),
      'value' => null,
    );
  }

  public function getRuleViewLabel() {
    return pht('Add Role Tags');
  }

  public function getRuleViewDescription($value) {
    return pht(
      'Add role tags: %s.',
      phutil_tag(
        'strong',
        array(),
        $this->getViewer()
          ->renderHandleList($value)
          ->setAsInline(true)
          ->render()));
  }

  public function getRuleViewIcon($value) {
    return id(new PHUIIconView())
      ->setIcon('fa-briefcase', 'green');
  }



}
