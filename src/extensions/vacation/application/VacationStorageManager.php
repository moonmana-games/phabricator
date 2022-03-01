<?php

class VacationStorageManager {
    
    public static function getVacationRules() {
        $dao = new VacationDAO();
        $connection = id($dao)->establishConnection('w');
        
        $rows = queryfx_all($connection,
            'SELECT * FROM vacation_vacationrules');
        
        return $rows;
    }
    
    public static function storeEarnedVacationHours($userID, $vacationHours) {
        $dao = new VacationDAO();
        $connection = id($dao)->establishConnection('w');
        
        $guard = AphrontWriteGuard::beginScopedUnguardedWrites();
        $dao->openTransaction();
        
        queryfx($connection,
            'INSERT INTO vacation_earnedhours SET userID = %d, earnedHours = earnedHours + %f
             ON DUPLICATE KEY UPDATE userID = %d, earnedHours = earnedHours + %f',
             $userID, $vacationHours, $userID, $vacationHours);
        
        $dao->saveTransaction();
        unset($guard);
    }
    
    public static function getEarnedVacationHours($user) {
        $dao = new VacationDAO();
        $connection = id($dao)->establishConnection('w');
        
        $rows = queryfx_all($connection,
            'SELECT earnedHours FROM vacation_earnedhours WHERE userID = %d', $user->getID());
        
        return floor($rows[0]['earnedHours']);
    }
    
    public static function storeSpentVacationHours($userID, $vacationHours) {
        $nowTimestamp = time();
        
        $dao = new VacationDAO();
        $connection = id($dao)->establishConnection('w');
        
        $guard = AphrontWriteGuard::beginScopedUnguardedWrites();
        $dao->openTransaction();
        
        queryfx($connection,
            'INSERT INTO vacation_spenthours SET userID = %d, spentHours = %d, dateWhenUsed = %d',
            $userID, $vacationHours, $nowTimestamp);
        
        $dao->saveTransaction();
        unset($guard);
    }
    
    public static function getSpentVacationHours($userID) {
        $dao = new VacationDAO();
        $connection = id($dao)->establishConnection('w');
        
        $rows = queryfx_all($connection,
            'SELECT * FROM vacation_spenthours WHERE userID = %d ORDER BY dateWhenUsed DESC', $userID);
        
        return $rows;
    }
}