<?php

final class VacationEventListener extends PhabricatorEventListener {

  public function register() {
      $this->listen(PhabricatorEventType::TYPE_TIME_TRACKED);
  }

  public function handleEvent(PhutilEvent $event) {
      $numHoursTracked = $event->getValue('numHoursTracked');
      $whenTrackedTimestamp = $event->getValue('whenTrackedTimestamp');
      
      $coefficient = VacationUtils::getVacationCoefficient($event->getUser());
      
      $vacationHours = $numHoursTracked / $coefficient;
      $userID = $event->getUser()->getID();
      
      VacationStorageManager::storeEarnedVacationHours($userID, $vacationHours);
  }
}
