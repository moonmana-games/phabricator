<?php

class TimeTrackerTimeUtils {
    
    const NUM_SECONDS_IN_DAY = 60 * 60 * 24;
    
    public static function getCurrentYear() {
        $date = new DateTime('@' . time());
        return PhutilTranslator::getInstance()->translateDate('Y', $date);
    }
    
    public static function getCurrentMonth() {
        $date = new DateTime('@' . time());
        return PhutilTranslator::getInstance()->translateDate('m', $date);
    }
    
    public static function getCurrentDay() {
        $date = new DateTime('@' . time());
        return PhutilTranslator::getInstance()->translateDate('d', $date);
    }
    
    public static function getTodayTimestamp() {
        $currentDay = TimeTrackerTimeUtils::getCurrentDay();
        $currentMonth = TimeTrackerTimeUtils::getCurrentMonth();
        $currentYear = TimeTrackerTimeUtils::getCurrentYear();
        
        return TimeTrackerTimeUtils::getTimestamp($currentDay, $currentMonth, $currentYear);
    }
    
    public static function getTimestamp($day, $month, $year) {
        return strtotime($year . '-' . $month . '-' . $day);
    }
}