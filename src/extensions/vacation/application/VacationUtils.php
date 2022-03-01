<?php

class VacationUtils {
    
    /* 
     * Return an object with user's seniority time
     * Eg. $interval->y, $interval->m, $interval->d
     */
    private static function getUserSeniority($user) {
        $registrationDate = date("Y-m-d", $user->getDateCreated());
        $now = date("Y-m-d");
        
        $d1 = new DateTime($registrationDate);
        $d2 = new DateTime($now);
        $interval = $d1->diff($d2);
        return $interval;
    }
    
    public static function getVacationCoefficient($user) {
        $rules = VacationStorageManager::getVacationRules();
        
        $userSeniority = self::getUserSeniority($user);
        $userSeniorityYears = $userSeniority->y;
        
        $currentMaxSeniority = 0;
        $currentBestCoefficient = 0;
        foreach ($rules as $rule) {
            if ($userSeniorityYears >= $rule['yearsOfSeniority'] && $rule['yearsOfSeniority'] >= $currentMaxSeniority) {
                $currentMaxSeniority = $rule['yearsOfSeniority'];
                $currentBestCoefficient = $rule['coefficient'];
            }
        }
        return $currentBestCoefficient;
    }
}