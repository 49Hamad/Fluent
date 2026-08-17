<?php

use Carbon\Carbon;

if (!function_exists('formatDateInArabic')) {
    function formatDateInArabic($dateString, $includeTime = false)
    {
        // Handle null or invalid date inputs
        if (!$dateString) {
            return 'غير متاح'; // Return a default message for null or invalid dates
        }

        try {
            // Parse the date string into a Carbon instance
            $date = Carbon::parse($dateString);
        } catch (\Exception $e) {
            return 'تاريخ غير صالح'; // Return a message indicating an invalid date
        }

        // Define Arabic translations for months and days
        $months = [
            'January' => 'يناير',
            'February' => 'فبراير',
            'March' => 'مارس',
            'April' => 'أبريل',
            'May' => 'مايو',
            'June' => 'يونيو',
            'July' => 'يوليو',
            'August' => 'أغسطس',
            'September' => 'سبتمبر',
            'October' => 'أكتوبر',
            'November' => 'نوفمبر',
            'December' => 'ديسمبر'
        ];

        $days = [
            'Sunday' => 'الأحد',
            'Monday' => 'الاثنين',
            'Tuesday' => 'الثلاثاء',
            'Wednesday' => 'الأربعاء',
            'Thursday' => 'الخميس',
            'Friday' => 'الجمعة',
            'Saturday' => 'السبت'
        ];

        // Determine the format based on whether time should be included
        $format = $includeTime ? 'l, d F Y, h:i a' : 'l, d F Y';

        // Format the date in English
        $formattedDate = $date->format($format);

        // Translate the day and month names to Arabic
        $formattedDate = str_replace(array_keys($days), array_values($days), $formattedDate);
        $formattedDate = str_replace(array_keys($months), array_values($months), $formattedDate);

        // Translate 'am' and 'pm' to Arabic time periods if time is included
        if ($includeTime) {
            $hour = $date->format('H'); // 24-hour format
            if ($hour >= 0 && $hour < 6) {
                $period = 'فجراً';
            } elseif ($hour >= 6 && $hour < 12) {
                $period = 'صباحاً';
            } elseif ($hour >= 12 && $hour < 15) {
                $period = 'ظهراً';
            } elseif ($hour >= 15 && $hour < 18) {
                $period = 'عصراً';
            } elseif ($hour >= 18 && $hour < 19) {
                $period = 'مغرب';
            } else {
                $period = 'ليلاً';
            }

            // Replace 'am' and 'pm' with the corresponding Arabic period
            $formattedDate = preg_replace('/\b(am|pm)\b/', $period, $formattedDate);
        }

        return $formattedDate;
    }
}
