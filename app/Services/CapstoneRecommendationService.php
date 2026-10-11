<?php

namespace App\Services;

class CapstoneRecommendationService
{
    /**
     * Generate three alternative capstone project ideas.
     *
     * These are rule-based suggestions, not AI-generated or
     * verified claims of originality.
     */
    public function recommend(
        string $title,
        string $abstract,
        array $similarStudies = [],
        array $reviewAreas = []
    ): array {
        $text = mb_strtolower($title . ' ' . $abstract, 'UTF-8');

        $ideas = $this->selectIdeas($text);

        $recommendations = [];

        foreach (array_slice($ideas, 0, 3) as $idea) {
            $recommendations[] = [
                'title' => $idea['title'],
                'abstract' => $idea['abstract'],
                'reason' => $idea['reason'],
                'design_note' => $this->buildDesignNote(
                    $reviewAreas
                ),
            ];
        }

        return $recommendations;
    }

    /**
     * Choose project directions based on the submitted study topic.
     */
    protected function selectIdeas(string $text): array
    {
        if (
            str_contains($text, 'attendance')
            || str_contains($text, 'absence')
            || str_contains($text, 'classroom presence')
        ) {
            return [
                [
                    'title' =>
                        'Predictive Student Absence Alert and Intervention System',
                    'abstract' =>
                        'This project proposes a student attendance analysis '
                        . 'system that examines historical attendance records '
                        . 'to identify recurring absence patterns. It provides '
                        . 'attendance summaries and configurable alerts to '
                        . 'help instructors identify students who may need '
                        . 'follow-up. The system also records intervention '
                        . 'actions and generates reports for reviewing '
                        . 'attendance trends over time.',
                    'reason' =>
                        'Focuses on absence-pattern analysis and follow-up '
                        . 'rather than only recording attendance.',
                ],
                [
                    'title' =>
                        'Student Attendance Analytics and Intervention Dashboard',
                    'abstract' =>
                        'This project develops a dashboard that consolidates '
                        . 'attendance records into visual summaries for '
                        . 'instructors and authorized school personnel. '
                        . 'It presents attendance trends, recurring absences, '
                        . 'class-level summaries, and downloadable reports. '
                        . 'The dashboard also allows authorized users to '
                        . 'document follow-up actions and review whether '
                        . 'attendance concerns have been addressed.',
                    'reason' =>
                        'Emphasizes analytics, reporting, and documented '
                        . 'follow-up instead of basic attendance capture.',
                ],
                [
                    'title' =>
                        'QR-Based Classroom Check-In with Attendance Audit Trail',
                    'abstract' =>
                        'This project proposes a classroom check-in platform '
                        . 'that uses time-limited QR codes to record student '
                        . 'attendance during scheduled class sessions. '
                        . 'The system stores check-in timestamps, maintains '
                        . 'an audit trail of attendance changes, and allows '
                        . 'authorized instructors to review and correct '
                        . 'records. It also generates session summaries and '
                        . 'reports to support transparent attendance '
                        . 'record management.',
                    'reason' =>
                        'Explores check-in verification and record auditing '
                        . 'as a distinct technical direction.',
                ],
            ];
        }

        if (
            str_contains($text, 'inventory')
            || str_contains($text, 'stock')
            || str_contains($text, 'retail')
        ) {
            return [
                [
                    'title' =>
                        'Inventory Demand Forecasting and Reorder Alert System',
                    'abstract' =>
                        'This project develops an inventory tool that uses '
                        . 'historical stock movement to estimate future '
                        . 'product demand. It provides configurable reorder '
                        . 'alerts, stock summaries, and reports that help '
                        . 'small businesses plan replenishment and reduce '
                        . 'the risk of stockouts or excess inventory.',
                    'reason' =>
                        'Shifts the focus from basic inventory recording '
                        . 'to demand planning and replenishment.',
                ],
                [
                    'title' =>
                        'Product Expiration and Batch Traceability Management System',
                    'abstract' =>
                        'This project proposes a system for recording product '
                        . 'batches, expiration dates, and stock movements. '
                        . 'It identifies items approaching expiration and '
                        . 'provides traceability reports to help authorized '
                        . 'staff locate affected batches and manage stock '
                        . 'rotation more consistently.',
                    'reason' =>
                        'Focuses on batch tracking, expiration monitoring, '
                        . 'and stock rotation.',
                ],
                [
                    'title' =>
                        'Multi-Branch Stock Transfer and Inventory Reconciliation System',
                    'abstract' =>
                        'This project develops a platform for managing stock '
                        . 'transfers between business branches. It records '
                        . 'transfer requests, dispatch and receipt details, '
                        . 'and inventory adjustments. Reconciliation reports '
                        . 'help staff compare recorded quantities and '
                        . 'investigate discrepancies between locations.',
                    'reason' =>
                        'Targets inter-branch transfers and reconciliation '
                        . 'rather than general inventory management.',
                ],
            ];
        }

        if (
            str_contains($text, 'restaurant')
            || str_contains($text, 'food ordering')
            || str_contains($text, 'reservation')
        ) {
            return [
                [
                    'title' =>
                        'Restaurant Order Preparation and Kitchen Queue System',
                    'abstract' =>
                        'This project develops a platform that organizes '
                        . 'incoming restaurant orders into a kitchen queue. '
                        . 'Staff can update preparation status, prioritize '
                        . 'orders, and monitor pending items through a shared '
                        . 'dashboard. Order timing summaries help management '
                        . 'review workflow bottlenecks and service efficiency.',
                    'reason' =>
                        'Concentrates on kitchen workflow and order preparation.',
                ],
                [
                    'title' =>
                        'Restaurant Ingredient Usage and Food Waste Tracking System',
                    'abstract' =>
                        'This project proposes a system that records ingredient '
                        . 'usage and food waste associated with restaurant '
                        . 'operations. It provides waste summaries, ingredient '
                        . 'consumption reports, and configurable notifications '
                        . 'for unusual usage patterns to support better '
                        . 'operational planning.',
                    'reason' =>
                        'Introduces ingredient usage and waste analysis as '
                        . 'a separate operational focus.',
                ],
                [
                    'title' =>
                        'Restaurant Service Feedback and Issue Resolution Portal',
                    'abstract' =>
                        'This project develops a portal where customers can '
                        . 'submit service feedback and report order-related '
                        . 'issues. Authorized restaurant staff can categorize '
                        . 'submissions, document resolutions, and review '
                        . 'summary reports to identify recurring service '
                        . 'concerns and opportunities for improvement.',
                    'reason' =>
                        'Focuses on feedback handling and service improvement '
                        . 'instead of ordering or table reservations.',
                ],
            ];
        }

        return [
            [
                'title' =>
                    'Research Workflow and Milestone Monitoring System',
                'abstract' =>
                    'This project proposes a platform for organizing research '
                    . 'tasks, milestones, deadlines, and progress updates. '
                    . 'Project members can document completed activities, '
                    . 'review upcoming requirements, and receive reminders '
                    . 'for approaching deadlines. Summary reports help '
                    . 'supervisors monitor project progress and identify '
                    . 'delayed activities.',
                'reason' =>
                    'Offers a project-management direction that can be '
                    . 'adapted to the submitted research domain.',
            ],
            [
                'title' =>
                    'Role-Based Research Document Review and Feedback System',
                'abstract' =>
                    'This project develops a document review platform with '
                    . 'role-based access for researchers and reviewers. '
                    . 'Users can submit document versions, record comments, '
                    . 'track revision status, and review feedback history. '
                    . 'The system maintains a record of review activities '
                    . 'to improve the organization and traceability of '
                    . 'research document revisions.',
                'reason' =>
                    'Emphasizes structured review, version history, and '
                    . 'feedback management.',
            ],
            [
                'title' =>
                    'Research Data Visualization and Report Generation Platform',
                'abstract' =>
                    'This project proposes a platform that organizes approved '
                    . 'research data and presents selected information using '
                    . 'charts, summaries, and downloadable reports. Authorized '
                    . 'users can filter data, compare relevant indicators, '
                    . 'and generate consistent reports to support project '
                    . 'monitoring and evidence-based decisions.',
                'reason' =>
                    'Provides a data-analysis direction that differs from '
                    . 'a system focused mainly on transaction or record entry.',
            ],
        ];
    }

    protected function buildDesignNote(array $reviewAreas): string
    {
        $areas = array_values(array_unique(array_filter(
            $reviewAreas,
            fn ($area) => is_string($area) && trim($area) !== ''
        )));

        if (empty($areas)) {
            return 'Before selecting this idea, verify its objectives, '
                . 'target users, scope, major features, and problem statement '
                . 'against existing repository studies.';
        }

        return 'For differentiation, review these areas in the proposed '
            . 'design: ' . implode(', ', $areas) . '.';
    }
}
