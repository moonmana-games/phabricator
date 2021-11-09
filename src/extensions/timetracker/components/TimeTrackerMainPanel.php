<?php

final class TimeTrackerMainPanel extends TimeTracker {

  public function getName() {
    return pht('Hours tracking');
  }
  
  protected function getPanelType() {
      return TimeTrackerPanelType::MAIN;
  }

  public function getDescription() {
      return phutil_safe_html('Here you can track your working time. Simply put amount of time you want to track and click SAVE.<br />
          You can track time for other days than today. Change the date for when you want to track your time, then save.<br />
          You can also deduct time, in case you made a mistake or tracked your time in wrong date.<br />
          To check how much time you already tracked, use <b>summary</b> page.');
  }
  
  public function renderPage($user) {

      $timeTrackingFormBox = $this->getTimeTrackingFormBox($user);
      
      $view = new PHUIInfoView();
      $view->setSeverity(PHUIInfoView::SEVERITY_NOTICE);
      $view->setTitle('Examples how to track your time');
      $view->setErrors(
          array(
              pht('8h'),
              pht('4h40m'),
              pht('4h 40m'),
              pht('35m'),
              phutil_safe_html('1-2 <i>(range, from 1-2 there will be 1hour tracked)</i>'),
              phutil_safe_html('22-2 <i>(range, from 22-2 there will be 4hour tracked)</i>'),
              phutil_safe_html('-8h <i>(deduct 8hours, use when you made a mistake)</i>'),
          ));
      $arr = array($view, $timeTrackingFormBox);
      
      $responseBox = $this->getResponseBox();
      if ($responseBox != null) {
          $arr[] = $responseBox;
          
          $date = $this->getRequest()->getStr('date');
          $pieces = explode('/', $date);
          
          $day = $pieces[1];
          $month = $pieces[0];
          $year = $pieces[2];
          
          $timestamp = TimeTrackerTimeUtils::getTimestamp($day, $month, $year);
          $dayHistoryDetails = new TimeTrackerDayHistoryDetailsBox($user->getID(), $timestamp);
          $box = $dayHistoryDetails->getDetailsBox();
          $arr[] = $box;
      }
      return $arr;
  }
  
  private function getTimeTrackingFormBox($user) {
      require_celerity_resource('jquery-js');
      require_celerity_resource('jquery-ui-js');
      require_celerity_resource('timetracker-js');
      require_celerity_resource('jquery-ui-css');
      
      $submit = id(new AphrontFormSubmitControl());
      $submit->setValue(pht('SAVE'))
          ->setControlStyle('width: 13%; margin-left: 3%;');
      
      $dateFormComponent = (id(new AphrontFormTextControl())
          ->setLabel(pht('Date:'))
          ->setDisableAutocomplete(true)
          ->setName('date')
          ->setValue($this->getCurrentDate())
          ->setControlStyle('width: 13%; margin-left: 3%;')
          ->setID('datepicker'));
      
      $form = id(new AphrontFormView())
        ->setUser($this->getRequest()->getUser())
        ->appendChild(id(new AphrontFormTextControl())
        ->setDisableAutocomplete(true)
        ->setControlStyle('width: 13%; margin-left: 3%;')
        ->setLabel(pht('Time:'))
        ->setName('timeTracked')
        ->setValue(''))
        ->addHiddenInput('isSent', '1')
        ->addHiddenInput('panelType', $this->getPanelType())
        ->appendChild($dateFormComponent)
        ->appendChild($submit);

      $box = id(new PHUIObjectBoxView())
        ->setForm($form)
        ->setHeaderText('Track your working time')
        ->appendChild(id(new PHUIBoxView()));
      
      return $box;
  }
  
  private function getCurrentDate() {
      $currentDay = TimeTrackerTimeUtils::getCurrentDay();
      $currentMonth = TimeTrackerTimeUtils::getCurrentMonth();
      $currentYear = TimeTrackerTimeUtils::getCurrentYear();
      return $currentMonth . '/' . $currentDay . '/' . $currentYear;
  }
  
  private function getResponseBox() {
      $handler = $this->getRequestHandler();
      if ($handler != null) {
          return $handler->getResponsePanel();
      }
      return null;
  }
}
