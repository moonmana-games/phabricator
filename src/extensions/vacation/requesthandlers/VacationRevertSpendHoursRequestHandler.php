<?php

class VacationRevertSpendHoursRequestHandler extends VacationRequestHandler  {
    
    private $request;
    private $responsePanel;
    
    public function handleRequest($request) {
        $this->request = $request;
        
        $rowID = $this->request->getStr('id');
        $row = $this->getSpentHoursRow($rowID);
        
        $success = $this->parseRequest($row);
        if ($success) {
            $userID = $row['userID'];
            $numHoursToRevert = $row['spentHours'];
            
            $this->removeSpentHourRow($rowID);
            VacationStorageManager::storeEarnedVacationHours($userID, $numHoursToRevert);
        }
    }
    
    private function parseRequest($row) {
        $now = time();
        $dateWhenSpent = $row['dateWhenUsed'];
        
        if ($now > $dateWhenSpent + VacationConfig::MAX_REVERT_TIME) {
            $this->responsePanel = $this->createResponsePanel(false);
            return false;
        }
        $this->responsePanel = $this->createResponsePanel(true);
        return true;
    }
    
    private function getSpentHoursRow($id) {
        $dao = new VacationDAO();
        $connection = id($dao)->establishConnection('w');
        
        $rows = queryfx_all($connection,
            'SELECT * FROM vacation_spenthours WHERE id = %d', $id);
        
        return $rows[0];
    }
    
    private function removeSpentHourRow($rowID) {
        $dao = new VacationDAO();
        $connection = id($dao)->establishConnection('w');
        
        $guard = AphrontWriteGuard::beginScopedUnguardedWrites();
        $dao->openTransaction();
        
        queryfx($connection,
            'DELETE FROM vacation_spenthours WHERE id = %d',
            $rowID);
        
        $dao->saveTransaction();
        unset($guard);
    }
    
    private function createResponsePanel($success) {
        $severity = $success ? PHUIInfoView::SEVERITY_SUCCESS : PHUIInfoView::SEVERITY_ERROR;
        $message = $success ? 'Successfully reverted' : 'Error. Cannot revert';
        
        $view = new PHUIInfoView();
        $view->setSeverity($severity);
        $view->setErrors(array(phutil_safe_html($message)));
        return $view;
    }
    
    public function getResponsePanel() {
        return $this->responsePanel;
    }
}