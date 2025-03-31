<?php
// hours_format.php

// Function to calculate hours worked from time_in and time_out
function calculateHoursWorked($time_in, $time_out) {
    // Convert the time_in and time_out to DateTime objects
    $time_in = new DateTime($time_in);
    $time_out = new DateTime($time_out);

    // Calculate the difference between time_in and time_out
    $interval = $time_in->diff($time_out);

    // Get the total hours and minutes worked
    $hours = $interval->h + ($interval->days * 24); // Include the days as hours
    $minutes = $interval->i;

    // Return the hours and minutes worked
    return ['hours' => $hours, 'minutes' => $minutes];
}

// Function to format the duty hours
function formatDutyHours($log) {
    // Get the calculated hours and minutes
    $worked_time = calculateHoursWorked($log['time_in'], $log['time_out']);
    $hours = $worked_time['hours'];
    $minutes = $worked_time['minutes'];

    // Display the formatted result
    if ($hours > 0) {
        if ($minutes > 0) {
            echo number_format($hours, 0) . " hr" . ($hours > 1 ? "s" : "") . " {$minutes} min";
        } else {
            echo number_format($hours, 0) . " hr" . ($hours > 1 ? "s" : "");
        }
    } else {
        // If there are no hours, display only minutes (if any)
        echo $minutes > 0 ? "{$minutes} min" : "0 min";
    }
}
?>