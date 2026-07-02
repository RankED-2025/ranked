import type { BadgeDistributionPoint } from '../../src/types/component/chart/badge-distribution'
import type { MyBadgePoint } from '../../src/types/component/chart/my-badges'
import type { MyBadgeDetail } from '../../src/types/component/chart/my-badges-detail'

export const badgeDistributionData: BadgeDistributionPoint[] = [
  { type: 'bronze', count: 10 },
  { type: 'argent', count: 5 },
]

export const badgeDistributionPoints: BadgeDistributionPoint[] = [
  { type: 'bronze', count: 12 },
  { type: 'or', count: 5 },
]

export const myBadgesData: MyBadgePoint[] = [
  { type: 'bronze', count: 2 },
  { type: 'or', count: 1 },
]

export const myBadgesPoints: MyBadgePoint[] = [
  { type: 'bronze', count: 3 },
  { type: 'or', count: 1 },
]

export const myBadgesDetailData: MyBadgeDetail[] = [
  { courseId: 1, courseTitle: 'Cours PHP', badgeType: 'or', badgeLabel: 'Or', percentage: 100 },
  { courseId: 2, courseTitle: 'Cours Maths', badgeType: 'bronze', badgeLabel: 'Bronze', percentage: 60 },
  { courseId: 3, courseTitle: 'Cours Histoire', badgeType: 'bronze', badgeLabel: 'Bronze', percentage: 30 },
]
