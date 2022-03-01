<?php

class VacationSpendHoursRequestHandler extends VacationRequestHandler  {
    
    private const ERROR_NON_NUMERIC = 'Error. Given input is not a decimal number.';
    private const ERROR_NOT_ENOUGH_VACATION = 'Error. You are trying to spend more vacation hours than you currently have.';
    private const ERROR_NEGATIVE_INPUT = 'Error. You cannot deduct negative amount of hours. Minimum is 1.';
    
    private $request;
    private $responsePanel;
    
    public function handleRequest($request) {
        $this->request = $request;
        
        $success = $this->parseRequest();
        if ($success) {
            $userID = $request->getUser()->getID();
            $amountHours = $request->getStr('amount');
            
            VacationStorageManager::storeSpentVacationHours($userID, $amountHours);
            VacationStorageManager::storeEarnedVacationHours($userID, -$amountHours);
        }
    }
    
    private function parseRequest() {
        $amountHoursToSpend = $this->request->getStr('amount');
        $earnedHours = VacationStorageManager::getEarnedVacationHours($this->request->getUser());
        
        if (!is_numeric($amountHoursToSpend) || is_float($amountHoursToSpend)) {
            $this->responsePanel = $this->createResponsePanel(false, self::ERROR_NON_NUMERIC);
            return false;
        }
        if ($amountHoursToSpend > $earnedHours) {
            $this->responsePanel = $this->createResponsePanel(false, self::ERROR_NOT_ENOUGH_VACATION);
            return false;
        }
        if ($amountHoursToSpend <= 0) {
            $this->responsePanel = $this->createResponsePanel(false, self::ERROR_NEGATIVE_INPUT);
            return false;
        }
        $this->responsePanel = $this->createResponsePanel(true);
        return true;
    }
    
    private function createResponsePanel($success, $errorMessage = '') {
        $severity = $success ? PHUIInfoView::SEVERITY_SUCCESS : PHUIInfoView::SEVERITY_ERROR;
        $message = $success ? 'Successfully spent <b>'. $this->request->getStr('amount') .'</b> vacation hours.' : $errorMessage;
        
        $view = new PHUIInfoView();
        $view->setSeverity($severity);
        $view->setErrors(array(phutil_safe_html($message)));
        return $view;
    }
    
    public function getResponsePanel() {
        return $this->responsePanel;
    }
}