<?php

class TimeTrackerMainPanelRequestHandler extends TimeTrackerRequestHandler {

    private $numMinutes = 0;
    private $numHours = 0;
    private $responsePanel = null;
    private $request = null;
    
    public function handleRequest($request) {
        $this->request = $request;
        
        $isSent = $request->getStr('isSent') == '1';
        if ($isSent) {
            $correctRequest = $this->parseTrackTimeRequest($request);
        
            if (!$correctRequest) {
                $this->responsePanel = $this->createResponsePanel(false);
            }
            else {
                $date = $request->getStr('date');
                $date = trim($date);
                $pieces = explode('/', $date);
                
                $day = $pieces[1];
                $month = $pieces[0];
                $year = $pieces[2];
                
                $manager = new TimeTrackerStorageManager();
                $manager->trackTime($request->getUser(), $this->numHours, $this->numMinutes, $day, $month, $year);
                
                $this->responsePanel = $this->createResponsePanel(true);
                
                $numHoursTracked = $this->numHours + $this->numMinutes / 60;
                $whenTrackedTimestamp = TimeTrackerTimeUtils::getTimestamp($day, $month, $year);
                $this->dispatchEvent($numHoursTracked, $whenTrackedTimestamp);
            }
        }
    }
    
    private function dispatchEvent($numHoursTracked, $whenTrackedTimestamp) {
        $eventType = PhabricatorEventType::TYPE_TIME_TRACKED;
        $eventData = array(
            'numHoursTracked' => $numHoursTracked,
            'whenTrackedTimestamp' => $whenTrackedTimestamp,
        );
        
        $event = id(new PhabricatorEvent($eventType, $eventData));
        $event->setUser($this->request->getUser());
        
        PhutilEventEngine::dispatchEvent($event);
    }
    
    public function parseTrackTimeRequest($request) {
        $timeTracked = $request->getStr('timeTracked');
        
        $timeTracked = trim($timeTracked);
        $timeTracked = strtolower($timeTracked);
        
        if (!strpbrk($timeTracked, '0123456789')) {
            return false;
        }
        
        $hasMinutes = strpos($timeTracked, 'm') !== false;
        $hasHours = strpos($timeTracked, 'h') !== false;
        $isNegative = strcmp(substr($timeTracked, 0, 1), '-') == 0;
        $isRange = (strpos($timeTracked, '-') !== false) && !$isNegative;
        
        if (!$hasMinutes && !$hasHours && !$isRange) {
            return false;
        }
        
        $correctInput = true;
        if ($isRange) {
            $correctInput = $this->parseRange($timeTracked);
        }
        else {
            $correctInput = $this->parseSingleTimeInput($timeTracked, $hasMinutes, $hasHours, $isNegative);
        }
        
        $numMinutesToTrack = $this->numMinutes + $this->numHours * 60;
        $numMinutesAlreadyTrackedToday = TimeTrackerStorageManager::getNumMinutesTrackedToday($request->getUser());//here bug, someone deducts time
        //for not today, while comparing to numMinsTrackedTODAY
        
        if ($numMinutesAlreadyTrackedToday + $numMinutesToTrack < 0 || $numMinutesToTrack == 0) {
            $correctInput = false;
        }
       
        return $correctInput;
    }
    
    private function parseSingleTimeInput($timeTracked, $hasMinutes, $hasHours, $isNegative) {
        if ($hasMinutes && $hasHours) {
            list($this->numHours, $this->numMinutes) = explode('h', $timeTracked);
            $this->numMinutes = trim(str_replace('m', '', $this->numMinutes));
        }
        else if ($hasMinutes && !$hasHours) {
            $pieces = explode('m', $timeTracked);
            $this->numMinutes = $pieces[0];
        }
        else if (!$hasMinutes && $hasHours) {
            $pieces = explode('h', $timeTracked);
            $this->numHours = $pieces[0];
        }
        
        $this->numMinutes = str_replace('-', '', $this->numMinutes);
        $this->numHours = str_replace('-', '', $this->numHours);
        
        if ($isNegative) {
            $this->numMinutes *= -1;
            $this->numHours *= -1;
        }
        return true;
    }
    
    private function parseRange($timeTracked) {
        $pieces = explode('-', $timeTracked);
        $from = trim($pieces[0]);
        $till = trim($pieces[1]);
        
        if ($from > 24 || $from < 0 || $till > 24 || $till < 0 || $from == $till) {
            return false;
        }
        
        if ($from > $till) {
            $this->numHours = 24 - $from + $till;
        }
        else {
            $this->numHours = $till - $from;
        }
        return true;
    }
    
    private function createResponsePanel($success) {
        $severity = $success ? PHUIInfoView::SEVERITY_SUCCESS : PHUIInfoView::SEVERITY_ERROR;
        $responseText = '';
        if ($success) {
            $responseText = 'Successfully tracked';
            if ($this->numHours != 0) {
                $responseText .= ' ' . $this->numHours . ' hours';
            }
            if ($this->numMinutes != 0) {
                $responseText .= ' ' . $this->numMinutes . ' minutes';
            }
        }
        else {
            $responseText = 'Incorrect input';
        }
        
        $view = new PHUIInfoView();
        $view->setSeverity($severity);
        $view->setErrors(array(pht($responseText)));
        return $view;
    }
    
    public function getResponsePanel() {
        return $this->responsePanel;
    }
    
    public function getRequest() {
        return $this->request;
    }
}
