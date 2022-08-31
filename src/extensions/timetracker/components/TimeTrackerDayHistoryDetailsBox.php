<?php

class TimeTrackerDayHistoryDetailsBox {
    private $userId;
    private $timestamp;
    
    public function __construct($userId, $timestamp) {
        $this->userId = $userId;
        $this->timestamp = $timestamp;
    }
    
    public function getDetailsBox() {
        $dao = new TimeTrackerTrackedTime();
        $connection = id($dao)->establishConnection('w');
        
        $result = queryfx_all(
            $connection,
            'SELECT numMinutes, realDateWhenTracked, projectPHID FROM timetracker_trackedtime WHERE userID = %d
             AND dateWhenTrackedFor = %d ORDER BY realDateWhenTracked ASC', $this->userId, $this->timestamp);
            
        $totalTrackedTime = 0;
        $data = array();
        foreach ($result as $row) {
            $totalTrackedTime += $row['numMinutes'];
            $row['realDateWhenTracked'] = date("Y-m-d  H:i:s", $row['realDateWhenTracked']);
            $row['numMinutes'] = $this->numMinutesToString($row['numMinutes']);
            $data[] = $row;
        }
            
        $totalTrackedTime = $this->numMinutesToString($totalTrackedTime);

        $list = new PHUIStatusListView();
        
        foreach ($data as $row) {
            $iconColor = ($row['numMinutes'] > 0) ? 'green' : 'red';

            $projectInfo = '';
            if($row['projectPHID'] != ''){
                $projectInfo .= " on ". TimeTrackerStorageManager::getNameSelectedProject($row['projectPHID']);
            }
            
            $list->addItem(id(new PHUIStatusItemView())
                ->setIcon(PHUIStatusItemView::ICON_CLOCK, $iconColor, pht(''))
                ->setTarget(pht($row['numMinutes'].$projectInfo))
                ->setNote(pht('tracked ' . $row['realDateWhenTracked'])));
        }
        
        $date = date('d-m-Y', $this->timestamp);
        
        $box = id(new PHUIObjectBoxView())
           ->setHeaderText('Tracked time history for ' . $date . ' (' . $totalTrackedTime . ' total)')
           ->appendChild($list);
        
        return $box;
    }
    
    private function numMinutesToString($numMinutes) {
        return TimeTrackerTimeUtils::numMinutesToString($numMinutes);
    }
}