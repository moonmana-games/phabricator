<?php

final class TimeTrackerSelectDateFormComponent extends AphrontFormControl {
    private $monthValue;
    private $dayValue;
    
    private function getMonthInputValue() {
        return $this->monthValue;
    }
    
    private function getDayInputValue() {
        return $this->dayValue;
    }
    
    protected function getCustomControlClass() {
        return 'aphront-form-control-text';
    }
    
    protected function renderInput() {
        if (!$this->getUser()) {
            throw new PhutilInvalidStateException('setUser');
        }
        
        $currentDay = TimeTrackerTimeUtils::getCurrentDay();
        $currentMonth = TimeTrackerTimeUtils::getCurrentMonth();
        $currentYear = TimeTrackerTimeUtils::getCurrentYear();
        
        $amountOfDaysInCurrentMonth = date('t', mktime(0, 0, 0, $currentMonth, 1, $currentYear));
        
        $days = range(1, $amountOfDaysInCurrentMonth);
        $days = array_fuse($days);
        
        $months = range($currentMonth, $currentMonth);
        $months = array_fuse($months);
        
        $years = range($currentYear, $currentYear);
        $years = array_fuse($years);
        
        $monthsSelect = AphrontFormSelectControl::renderSelectTag(
            $currentMonth,
            $months,
            array(
                'sigil' => 'month-input',
                'name' => 'month-input',
            ));
        
        $daysSelect = AphrontFormSelectControl::renderSelectTag(
            $currentDay,
            $days,
            array(
                'sigil' => 'day-input',
                'name' => 'day-input',
            ));
        
        $yearsSelect = AphrontFormSelectControl::renderSelectTag(
            $currentYear,
            $years,
            array(
                'sigil' => 'year-input',
                'name' => 'year-input',
            ));
        
        return hsprintf('%s %s %s', $daysSelect, $monthsSelect, $yearsSelect);
    }
}
