<?php

class TimeTrackerDayDetailsRequestHandler extends TimeTrackerRequestHandler {
    
    private $detailsData;
    private $totalTrackedTime = 0;
    
    public function handleRequest($request) {
        
        $dayTimestamp = $this->getTimestampFromInput($request->getStr('day'));
        $userID = $request->getUser()->getID();
            
        $dao = new TimeTrackerDAO();
        $connection = id($dao)->establishConnection('w');
            
        $result = queryfx_all(
            $connection,
            'SELECT numMinutes, realDateWhenTracked FROM timetracker_trackedtime WHERE userID = %d
             AND dateWhenTrackedFor = %d ORDER BY realDateWhenTracked ASC', $userID, $dayTimestamp);
                
        $data = array();
        foreach ($result as $row) {
            $this->totalTrackedTime += $row['numMinutes'];
            $row['realDateWhenTracked'] = date("Y-m-d  H:i:s", $row['realDateWhenTracked']);
            $row['numMinutes'] = $this->numMinutesToString($row['numMinutes']);
            $data[] = $row;
        }
        
        $this->detailsData = $data;
        $this->totalTrackedTime = $this->numMinutesToString($this->totalTrackedTime);
    }
    
    public function getDetailsData() {
        return $this->detailsData;
    }
    
    public function getTotalTrackedTime() {
        return $this->totalTrackedTime;
    }
    
    private function getTimestampFromInput($dateInput) {
        $dateInput = trim($dateInput);
        $pieces = explode('-', $dateInput);
        
        $day = $pieces[0];
        $month = $pieces[1];
        $year = $pieces[2];
        
        return TimeTrackerTimeUtils::getTimestamp($day, $month, $year);
    }
    
    private function numMinutesToString($numMinutes) {
        if ($numMinutes < 60) {
            return $numMinutes . ' minutes';
        }
        
        $numHours = floor($numMinutes / 60);
        $remainingMinutes = $numMinutes % 60;
        
        $str = $numHours . ' hours';
        if ($remainingMinutes > 0) {
            $str .= ' ' . $remainingMinutes . ' minutes';
        }
        return $str;
    }
}
