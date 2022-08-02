<?php

class TimeTrackerStorageManager {

  public function trackTime($user, $numHours, $numMinutes, $day, $month, $year) {
      $userID = $user->getID();
      $numMinutesToTrack = $this->getNumMinutesToTrack($numHours, $numMinutes);
      
      $timestampWhenTrackedFor = strtotime($year . '-' . $month . '-' . $day);
      
      $dao = new TimeTrackerTrackedTime();
      $connection = id($dao)->establishConnection('w');
      $guard = AphrontWriteGuard::beginScopedUnguardedWrites();
      $dao->openTransaction();
      
      queryfx($connection, 'INSERT INTO timetracker_trackedtime SET 
        numMinutes = %d, realDateWhenTracked = %d, userID = %d, dateWhenTrackedFor = %d', 
          $numMinutesToTrack, time(), $userID, $timestampWhenTrackedFor);
      
      $dao->saveTransaction();
      unset($guard);
  }
  
  public static function getNumMinutesTrackedToday($user) {
      $todayTimestamp = TimeTrackerTimeUtils::getTodayTimestamp();
      $dao = new TimeTrackerTrackedTime();
      $connection = id($dao)->establishConnection('w');

      $rows = queryfx_all(
          $connection,
          'SELECT numMinutes FROM timetracker_trackedtime WHERE dateWhenTrackedFor = %d AND userID = %d',
          $todayTimestamp,
          $user->getID());
      
      $totalMinutes = 0;
      foreach ($rows as $row) {
          $totalMinutes += $row['numMinutes'];
      }
      return $totalMinutes;
  }

  public static function getNumMinutesTrackedFromDate($user, $date) {
    $dao = new TimeTrackerTrackedTime();
    $connection = id($dao)->establishConnection('w');

    $rows = queryfx_all(
        $connection,
        'SELECT numMinutes FROM timetracker_trackedtime WHERE dateWhenTrackedFor = %d AND userID = %d',
        $date,
        $user->getID());
    
    $totalMinutes = 0;
    foreach ($rows as $row) {
        $totalMinutes += $row['numMinutes'];
    }
    return $totalMinutes;
}
  
  private function getNumMinutesToTrack($numHours, $numMinutes) {
      return $numHours * 60 + $numMinutes;
  }
}
