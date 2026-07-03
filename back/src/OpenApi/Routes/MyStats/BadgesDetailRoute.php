<?php

namespace App\OpenApi\Routes\MyStats;

class BadgesDetailRoute extends AbstractMyStatsDetailRoute
{
    protected function getPath(): string
    {
        return '/api/my-stats/badges-detail';
    }

    protected function getOperationId(): string
    {
        return 'getMyBadgesDetail';
    }

    protected function getSummary(): string
    {
        return 'Get the authenticated student\'s badge details per course';
    }

    protected function getSuccessDescription(): string
    {
        return 'Badge details for each enrolled course';
    }

    protected function getItemProperties(): array
    {
        return [
            'courseId'    => ['type' => 'integer'],
            'courseTitle' => ['type' => 'string'],
            'badgeType'   => ['type' => 'string'],
            'badgeLabel'  => ['type' => 'string'],
            'percentage'  => ['type' => 'integer'],
        ];
    }
}
