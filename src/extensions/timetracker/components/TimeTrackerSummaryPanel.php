<?php

final class TimeTrackerSummaryPanel extends TimeTracker {

  public function getName() {
      return pht('Summary');
  }

  public function getDescription() {
      return phutil_safe_html('Pick the date range and click GO to check your working hours between those dates.<br />
                  Click on concrete day to view tracked time history.');
  }
  
  protected function getPanelType() {
      return TimeTrackerPanelType::SUMMARY;
  }
  
  public function renderPage($user) {

      $dateRangeFormBox = $this->getDateRangeFormBox($user);
      $chartBox = $this->getChartBox();
      $dayDetailsBox = $this->getDayDetailsBox();
      
      $elements = array();
      $elements[] = $dateRangeFormBox;
      if ($chartBox != null) {
          $elements[] = $chartBox;
      }
      if ($dayDetailsBox != null) {
          $elements[] = $dayDetailsBox;
      }
      return phutil_tag_div('ml', $elements);
  }
  
  private function getDateRangeFormBox($user) {
      require_celerity_resource('jquery-js');
      require_celerity_resource('jquery-ui-js');
      require_celerity_resource('timetracker-js');
      require_celerity_resource('jquery-ui-css');
      
      $submit = id(new AphrontFormSubmitControl());
      $submit->setValue(pht('Go'));
      
      $fromDateInput = (id(new AphrontFormTextControl())
          ->setLabel(pht('From:'))
          ->setDisableAutocomplete(true)
          ->setName('from')
          ->setValue('')
          ->setID('datepicker'));
      
      $toDateInput = (id(new AphrontFormTextControl())
          ->setLabel(pht('To:'))
          ->setDisableAutocomplete(true)
          ->setName('to')
          ->setValue('')
          ->setID('datepicker2'));
      
      $form = id(new AphrontFormView())
        ->setUser($this->getRequest()->getUser())
        ->appendChild($fromDateInput)
        ->appendChild($toDateInput)
        ->addHiddenInput('isSent', '1')
        ->addHiddenInput('panelType', $this->getPanelType())
        ->appendChild($submit);
          
      $box = id(new PHUIObjectBoxView())
        ->setForm($form)
        ->appendChild(id(new PHUIBoxView()));
      
      return $box;
  }
  
  private function getChartBox() {
      $requestHandler = $this->getRequestHandler();
      if ($requestHandler == null || !($requestHandler instanceof TimeTrackerSummaryPanelRequestHandler)) {
          return null;
      }
      
      $chartJsonData = $requestHandler->getChartJsonData();
      
      $map = CelerityResourceMap::getNamedInstance('phabricator');
      $chart = CelerityAPI::getStaticResourceResponse()->getURI($map, 'rsrc/js/application/timetracker/chart/Chart.min.js');
      $jquery = CelerityAPI::getStaticResourceResponse()->getURI($map, 'rsrc/js/application/timetracker/chart/jquery.min.js');
      $showGraph = CelerityAPI::getStaticResourceResponse()->getURI($map, 'rsrc/js/application/timetracker/chart/show-graph.js');
      
      $content = phutil_safe_html(pht('<div id="chart-container">
        <canvas id="graphCanvas"></canvas></div>
        <script type=text/javascript src="%s"></script>
        <script type=text/javascript src="%s"></script>
        <script type=text/javascript src="%s"></script>
        <script type=text/javascript>showGraph(%s);</script>', $jquery, $chart, $showGraph, $chartJsonData));
      
      $box = id(new PHUIObjectBoxView())
        ->setHeader('Title')
        ->appendChild($content)
        ->appendChild(id(new PHUIBoxView()));
      
      return $box;
  }
  
  private function getDayDetailsBox() {
      $requestHandler = $this->getRequestHandler();
      if ($requestHandler == null || !($requestHandler instanceof TimeTrackerDayDetailsRequestHandler)) {
          return null;
      }
      
      $detailsData = $requestHandler->getDetailsData();
      $totalTrackedTime = $requestHandler->getTotalTrackedTime();
      
      $list = new PHUIStatusListView();
      
      foreach ($detailsData as $row) {
          $iconColor = ($row['numMinutes'] > 0) ? 'green' : 'red';
          $list->addItem(id(new PHUIStatusItemView())
              ->setIcon(PHUIStatusItemView::ICON_CLOCK, $iconColor, pht(''))
              ->setTarget(pht($row['numMinutes']))
              ->setNote(pht('tracked ' . $row['realDateWhenTracked'])));
      }
      
      $day = $_GET['day'];
      $box = id(new PHUIObjectBoxView())
          ->setHeaderText('Tracked time history for ' . $day)
          ->appendChild($list);
                    
      return $box;
  }
}
