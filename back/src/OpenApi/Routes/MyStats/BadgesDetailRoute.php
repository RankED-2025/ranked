<?php

namespace App\OpenApi\Routes\MyStats;

class BadgesDetailRoute extends AbstractMyStatsDetailRoute
{
    public function __construct()
    {
        parent::__construct(
            path: '/api/my-stats/badges-detail',
            operationId: 'getMyBadgesDetail',
            summary: 'Get the authenticated student\'s badge details per course',
            successDescription: 'Badge details for each enrolled course',
            itemProperties: [
                'courseId'    => ['type' => 'integer'],
                'courseTitle' => ['type' => 'string'],
                'badgeType'   => ['type' => 'string'],
                'badgeLabel'  => ['type' => 'string'],
                'percentage'  => ['type' => 'integer'],
            ],
        );
    }
}
