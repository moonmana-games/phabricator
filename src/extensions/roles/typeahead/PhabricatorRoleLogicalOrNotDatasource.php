<?php

final class PhabricatorRoleLogicalOrNotDatasource
  extends PhabricatorTypeaheadCompositeDatasource {

  public function getBrowseTitle() {
    return pht('Browse Roles');
  }

  public function getPlaceholderText() {
    return pht('Type any(<role>) or not(<role>)...');
  }

  public function getDatasourceApplicationClass() {
    return 'PhabricatorRoleApplication';
  }

  public function getComponentDatasources() {
    return array(
      new PhabricatorRoleDatasource(),
    );
  }

  public function getDatasourceFunctions() {
    return array(
      'any' => array(
        'name' => pht('In Any: ...'),
        'arguments' => pht('role'),
        'summary' => pht('Find results in any of several roles.'),
        'description' => pht(
          'This function allows you to find results in one of several '.
          'roles. Another way to think of this function is that it '.
          'allows you to perform an "or" query.'.
          "\n\n".
          'By default, if you enter several roles, results are returned '.
          'only if they belong to all of the roles you enter. That is, '.
          'this query will only return results in //both// roles:'.
          "\n\n".
          '> ios, android'.
          "\n\n".
          'If you want to find results in any of several roles, you can '.
          'use the `any()` function. For example, you can use this query  to '.
          'find results which are in //either// role:'.
          "\n\n".
          '> any(ios), any(android)'.
          "\n\n".
          'You can combine the `any()` function with normal role tokens '.
          'to refine results. For example, use this query to find bugs in '.
          '//either// iOS or Android:'.
          "\n\n".
          '> bug, any(ios), any(android)'),
      ),
      'not' => array(
        'name' => pht('Not In: ...'),
        'arguments' => pht('role'),
        'summary' => pht('Find results not in specific roles.'),
        'description' => pht(
          'This function allows you to find results which are not in '.
          'one or more roles. For example, use this query to find '.
          'results which are not associated with a specific role:'.
          "\n\n".
          '> not(vanilla)'.
          "\n\n".
          'You can exclude multiple roles. This will cause the query '.
          'to return only results which are not in any of the excluded '.
          'roles:'.
          "\n\n".
          '> not(vanilla), not(chocolate)'.
          "\n\n".
          'You can combine this function with other functions to refine '.
          'results. For example, use this query to find iOS results which '.
          'are not bugs:'.
          "\n\n".
          '> ios, not(bug)'),
      ),
    );
  }

  protected function didLoadResults(array $results) {
    $function = $this->getCurrentFunction();
    $return_any = ($function !== 'not');
    $return_not = ($function !== 'any');

    $return = array();
    foreach ($results as $result) {
      $result
        ->setTokenType(PhabricatorTypeaheadTokenView::TYPE_FUNCTION)
        ->setIcon('fa-asterisk')
        ->setColor(null)
        ->resetAttributes()
        ->addAttribute(pht('Function'));

      if ($return_any) {
        $return[] = id(clone $result)
          ->setPHID('any('.$result->getPHID().')')
          ->setDisplayName(pht('In Any: %s', $result->getDisplayName()))
          ->setName('any '.$result->getName())
          ->addAttribute(pht('Include results tagged with this role.'));
      }

      if ($return_not) {
        $return[] = id(clone $result)
          ->setPHID('not('.$result->getPHID().')')
          ->setDisplayName(pht('Not In: %s', $result->getDisplayName()))
          ->setName('not '.$result->getName())
          ->addAttribute(pht('Exclude results tagged with this role.'));
      }
    }

    return $return;
  }

  protected function evaluateFunction($function, array $argv_list) {
    $phids = array();
    foreach ($argv_list as $argv) {
      $phids[] = head($argv);
    }

    $operator = array(
      'any' => PhabricatorQueryConstraint::OPERATOR_OR,
      'not' => PhabricatorQueryConstraint::OPERATOR_NOT,
    );

    $results = array();
    foreach ($phids as $phid) {
      $results[] = new PhabricatorQueryConstraint(
        $operator[$function],
        $phid);
    }

    return $results;
  }

  public function renderFunctionTokens($function, array $argv_list) {
    $phids = array();
    foreach ($argv_list as $argv) {
      $phids[] = head($argv);
    }

    $tokens = $this->renderTokens($phids);
    foreach ($tokens as $token) {
      $token->setColor(null);
      if ($token->isInvalid()) {
        if ($function == 'any') {
          $token->setValue(pht('In Any: Invalid Role'));
        } else {
          $token->setValue(pht('Not In: Invalid Role'));
        }
      } else {
        $token
          ->setIcon('fa-asterisk')
          ->setTokenType(PhabricatorTypeaheadTokenView::TYPE_FUNCTION);

        if ($function == 'any') {
          $token
            ->setKey('any('.$token->getKey().')')
            ->setValue(pht('In Any: %s', $token->getValue()));
        } else {
          $token
            ->setKey('not('.$token->getKey().')')
            ->setValue(pht('Not In: %s', $token->getValue()));
        }
      }
    }

    return $tokens;
  }

}
