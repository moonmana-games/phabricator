<?php

final class VacationMainPage extends Vacation {

  public function getName() {
    return pht('Vacation');
  }

  public function getDescription() {
      return phutil_safe_html('Here you can check how many available holiday hours you currently have');
  }
  
  public function renderPage($user) {

      $vacationInfoBox = $this->getVacationInfoBox($user);
      $spendVacationBox = $this->getSpendVacationBox();
      $responseBox = $this->getResponseBox();
      
      $boxes = array($vacationInfoBox, $spendVacationBox);
      if ($responseBox != null) {
          $boxes[] = $responseBox;
      }
      return $boxes;
  }
  
  private function getVacationInfoBox($user) {
      $earnedVacationHours = VacationStorageManager::getEarnedVacationHours($user);
      
      $vacationInfoBox = new PHUIInfoView();
      $vacationInfoBox->setSeverity(PHUIInfoView::SEVERITY_NOTICE);
      $vacationInfoBox->setTitle('Info');
      $vacationInfoBox->setErrors(
          array(
              phutil_safe_html('Earned vacation hours: <b>' . $earnedVacationHours . '</b>'),
          ));
      return $vacationInfoBox;
  }
  
  private function getSpendVacationBox() {
      $submit = id(new AphrontFormSubmitControl());
      $submit->setValue(pht('SPEND'))
         ->setControlStyle('width: 20%; margin-left: 3%;');
      
      $input = (id(new AphrontFormTextControl())
         ->setLabel(pht('Hours:'))
         ->setDisableAutocomplete(true)
         ->setName('amount')
         ->setControlStyle('width: 20%; margin-left: 3%;'));
      
      $form = id(new AphrontFormView())
         ->setUser($this->getRequest()->getUser())
         ->addHiddenInput('isSent', '1')
         ->addHiddenInput('formType', VacationFormType::SPEND_HOURS)
         ->appendChild($input)
         ->appendChild($submit);
      
      $spendVacationBox = id(new PHUIObjectBoxView())
         ->setForm($form)
         ->setHeaderText('Spend your vacation hours')
         ->appendChild(id(new PHUIBoxView()));
      
      return $spendVacationBox;
  }
  
  private function getResponseBox() {
      $handler = $this->getRequestHandler();
      if ($handler != null) {
          return $handler->getResponsePanel();
      }
      return null;
  }
}