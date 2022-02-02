<?php

final class VacationMainPage extends Vacation {

  public function getName() {
    return pht('Vacation');
  }

  public function getDescription() {
      return phutil_safe_html('Title');
  }
  
  public function renderPage($user) {

      return '';
  }
}
