<?php

namespace AlphaAlpaka\Defaults;

/**
 * Class CronJobScheduler
 *
 * Utility class for managing custom WP Cron job intervals.
 */
class CronJobScheduler
{
    /**
     * Registers the hook to add custom WP Cron intervals.
     *
     * @return void
     */
    public static function registerSchedule()
    {
        add_filter('cron_schedules', [self::class, 'addCustomCronIntervals']);
    }

    /**
     * Adds custom intervals to WP Cron schedules.
     *
     * @param array $schedules Existing cron schedules.
     * @return array Updated cron schedules with new intervals.
     */
    public static function addCustomCronIntervals($schedules)
    {
        // Add an 'Every Minute' interval
        $schedules['every_minute'] = [
            'interval' => 60,
            'display'  => __('Every Minute', 'alpha-alpaka'),
        ];

        // Add an 'Every Week' interval
        $schedules['every_week'] = [
            'interval' => 60 * 60 * 24 * 7,
            'display'  => __('Every Week', 'alpha-alpaka'),
        ];

        return $schedules;
    }
}

// Register the custom WP Cron intervals.
add_action('init', [CronJobScheduler::class, 'registerSchedule']);
