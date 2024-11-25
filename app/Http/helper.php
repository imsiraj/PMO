<?php

if (!function_exists('replace_newline')) {
    function replace_newline($string)
    {
        return str_replace(["\r\n", "\r", "\n"], ' ', $string);
    }
}

if (!function_exists('shortcode')) {

    function shortcode($args, $string)
    {
        foreach ($args as $key => $value) {
            $string = str_replace($key, $value, $string);
        }
        return $string;
    }
}
if (!function_exists('validate_row_data')) {

    function validate_row_data($rowData, $rowKeys, $defaultKey)
    {
        $index = array_search($defaultKey, $rowKeys);

        if ($index !== false && isset($rowData[$index])) {
            return replace_newline(trim($rowData[$index]));
        } else {
            return NULL;
        }
    }
}
if (!function_exists('format_number_two_decimals')) {

    function format_number_two_decimals($number)
    {
        return number_format($number, 2, '.', '');
    }
}
if (!function_exists('get_super_admin_details')) {
    function get_super_admin_details()
    {
        return [
            'name' => env('SUPER_ADMIN_NAME'),
            'email' => env('SUPER_ADMIN_EMAIL'),
            'password' => env('SUPER_ADMIN_PASSWORD'),
        ];
    }
}

if (!function_exists('get_friday_date')) {
    function get_friday_date($inputDate)
    {
        $date = new DateTime($inputDate);
        if ($date->format('N') == 5) {
            $date->modify('+7 days');
        } else {
            $date->modify('+7 days');
            $date->modify('next friday');
        }
        return $date->format('Y-m-d');
    }
}

if (!function_exists('get_last_friday_date')) {
    function get_last_friday_date($inputDate)
    {
        $date = new DateTime($inputDate);

        if ($date->format('N') == 5) {
            $date->modify('-7 days');
        } else {
            $date->modify('last friday');
        }
        return $date->format('Y-m-d');
    }
}

if (!function_exists('get_dates_by_week')) {
    function get_dates_by_week($searchForDate, $importedOn)
    {
        if ($searchForDate <= $importedOn) {
            $searchForDate = $importedOn;
        }
        return $searchForDate;
    }
}

if (!function_exists('mapPercentage')) {
    function mapPercentage($percentages, $key)
    {
        $percentageMap = [];
        foreach ($percentages as $percentage) {
            $percentageMap[$percentage['title']] = $percentage[$key]['value'] ?? 0.0;
        }
        return $percentageMap;
    }
}

if (!function_exists('mapStatusSprintSizes')) {
    function mapStatusSprintSizes($sizes)
    {
        $mappedSizes = [];
        foreach ($sizes as $size) {
            $mappedSizes[$size->title] = $size->sprint_total;
        }
        return $mappedSizes;
    }
}

if (!function_exists('findSprintTotalByTitle')) {
    function findSprintTotalByTitle($sprintData, $title)
    {
        foreach ($sprintData as $data) {
            if ($data->title === $title) {
                return $data->sprint_total;
            }
        }
        return 0;
    }
}

if (!function_exists('calculateDaysPassed')) {
    function calculateDaysPassed($startDate, $currentDate)
    {
        $startDateObj = new DateTime($startDate);
        $currentDateObj = new DateTime($currentDate);
        $interval = $startDateObj->diff($currentDateObj);
        return $interval->days;
    }
}

if (!function_exists('calculateSprintRatio')) {
    function calculateSprintRatio($daysPassed, $totalSpirntDays)
    {
        $ratio = (float) ($daysPassed / $totalSpirntDays);
        return round($ratio * 2) / 2;
    }
}

if (!function_exists('calculateWeeksPassed')) {
    function calculateWeeksPassed($daysPassed)
    {
        return ceil($daysPassed / 7);
    }
}

if (!function_exists('getAllFridays')) {
    function getAllFridays($startDate)
    {
        $fridays = [];
        $start = new DateTime($startDate);
        $end = new DateTime('2024-10-04');

        // Check if the start date is a Friday
        if ($start->format('N') == 5) {
            // If it is Friday, we want to include it in the list
            $fridays[] = $start->format('Y-m-d');
        } else {
            // Move to the next Friday
            $start->modify('next friday');
        }

        // Loop through the Fridays until the end date
        while ($start <= $end) {
            $fridays[] = $start->format('Y-m-d');
            $start->modify('+1 week');
        }

        return $fridays;
    }
}
