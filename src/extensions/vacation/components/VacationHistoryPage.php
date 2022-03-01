<?php

final class VacationHistoryPage extends Vacation {

  public function getName() {
    return pht('History');
  }

  public function getDescription() {
      return phutil_safe_html('History');
  }
  
  public function renderPage($user) {

      $request = $this->getRequest();
      
      $data = VacationStorageManager::getSpentVacationHours($request->getUser()->getID());
      
      $offset = (int)$request->getInt('offset');
      $pageSize = 10;
      $itemCount = count($data);
      
      $rows = array();
      for ($i = $offset; $i < min($itemCount, $offset + $pageSize); $i++) {
          $oneItemData = $data[$i];
          $rows[] = array($this->getHistoryItem($oneItemData));
      }
      
      $table = new AphrontTableView($rows);
      $table->setZebraStripes(false);
      $panel = new PHUIObjectBoxView();
      $panel->setHeaderText(pht('Example'));
      $panel->appendChild($table);
      
      $pager = new PHUIPagerView();
      $pager->setPageSize($pageSize);
      $pager->setOffset($offset);
      $pager->setCount($itemCount);
      $pager->setURI($request->getRequestURI(), 'offset');
      $panel->appendChild($pager);
      
      $panels = array();
      $panels[] = $panel;
      
      $responseBox = $this->getResponseBox();
      if ($responseBox != null) {
          $panels[] = $responseBox;
      }
      
      return $panels;
  }
  
  private function getHistoryItem($data) {
      $date = date("Y/m/d", $data['dateWhenUsed']);
      $hoursText = $data['spentHours'] == 1 ? 'hour' : 'hours';
      $text = $date .' spent  '. $data['spentHours'] .' '. $hoursText;
      
      $submit = id(new AphrontFormSubmitControl());
      $submit->setValue(pht('REVERT'))
         ->setControlStyle('width: 20%; margin: 0%;');
      
      $form = id(new AphrontFormView())
         ->setUser($this->getRequest()->getUser())
         ->addHiddenInput('formType', VacationFormType::REVERT_SPEND_HOURS)
         ->addHiddenInput('id', $data['id'])
         ->appendChild($submit);
      
      $box = id(new PHUIObjectBoxView())
         ->setHeaderText(phutil_safe_html($text));
      
      if (time() < $data['dateWhenUsed'] + VacationConfig::MAX_REVERT_TIME) {
         $box->appendChild($form);
      }
      return $box;
  }
  
  private function getResponseBox() {
      $handler = $this->getRequestHandler();
      if ($handler != null) {
          return $handler->getResponsePanel();
      }
      return null;
  }
}