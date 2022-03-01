<?php

final class VacationAdminManageHoursPage extends Vacation {

  public function getName() {
    return pht('Admin manage hours');
  }

  public function getDescription() {
      return phutil_safe_html('Admin page');
  }
  
  public function renderPage($user) {

      $request = $this->getRequest();
      
//       if ($object instanceof PhabricatorPolicyInterface) {
//           $can_view = PhabricatorPolicyFilter::hasCapability(
//               $user,
//               $object,
//               PhabricatorPolicyCapability::CAN_VIEW);
      
      $panels = array();
      return $panels;
  }
  
  private function getResponseBox() {
      $handler = $this->getRequestHandler();
      if ($handler != null) {
          return $handler->getResponsePanel();
      }
      return null;
  }
}