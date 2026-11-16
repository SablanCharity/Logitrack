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

        if (
            str_contains($text, 'health')
            || str_contains($text, 'clinic')
            || str_contains($text, 'patient')
            || str_contains($text, 'medical')
            || str_contains($text, 'hospital')
        ) {
            return [
                [
                    'title' => 'Clinic Appointment and Patient Queue Management System',
                    'abstract' => 'This project organizes clinic appointments, walk-in patients, and service queues. Staff can monitor waiting times, update visit status, and review daily service summaries. Patient information is limited to authorized users and recorded according to the clinic\'s workflow.',
                    'reason' => 'Focuses on appointment coordination and queue visibility rather than electronic diagnosis or treatment.',
                ],
                [
                    'title' => 'Medicine Stock and Expiration Monitoring System for Community Clinics',
                    'abstract' => 'This project records medicine deliveries, dispensing, and remaining quantities for a community clinic. It highlights low-stock items and medicines nearing expiration, and provides movement reports to support replenishment and stock rotation.',
                    'reason' => 'Targets medicine availability and expiration control as a focused clinic operations problem.',
                ],
                [
                    'title' => 'Community Health Follow-Up and Reminder Platform',
                    'abstract' => 'This project helps authorized health workers schedule and document follow-up visits for community health programs. It tracks pending follow-ups, records contact attempts, and summarizes completion rates without making automated clinical decisions.',
                    'reason' => 'Emphasizes follow-up coordination and program monitoring while keeping clinical decisions with health professionals.',
                ],
            ];
        }

        if (
            str_contains($text, 'farm')
            || str_contains($text, 'agriculture')
            || str_contains($text, 'crop')
            || str_contains($text, 'livestock')
            || str_contains($text, 'irrigation')
        ) {
            return [
                [
                    'title' => 'Crop Planting Calendar and Farm Activity Monitoring System',
                    'abstract' => 'This project helps growers plan planting activities by recording crop cycles, field tasks, and target dates. It provides reminders and seasonal activity summaries so farm operators can monitor planned and completed work.',
                    'reason' => 'Focuses on farm activity planning and record keeping rather than general farm administration.',
                ],
                [
                    'title' => 'Farm Produce Traceability and Harvest Record Platform',
                    'abstract' => 'This project records harvest batches, farm locations, handling dates, and distribution details. Users can trace produce movement and generate batch reports to support quality checks and more organized distribution.',
                    'reason' => 'Adds batch traceability from harvest through distribution as a distinct project direction.',
                ],
                [
                    'title' => 'Irrigation Schedule and Water Usage Logging System',
                    'abstract' => 'This project allows farm personnel to schedule irrigation activities and log water usage by plot and crop cycle. Reports compare planned schedules with recorded usage to help identify missed activities and review consumption patterns.',
                    'reason' => 'Narrows the scope to irrigation scheduling and water-use records.',
                ],
            ];
        }

        if (
            str_contains($text, 'library')
            || str_contains($text, 'book borrowing')
            || str_contains($text, 'lending')
        ) {
            return [
                [
                    'title' => 'Library Seat Reservation and Occupancy Monitoring System',
                    'abstract' => 'This project allows library users to reserve study spaces and helps staff monitor seat availability. It records reservation periods, check-ins, and no-shows, then provides occupancy summaries to support space planning.',
                    'reason' => 'Addresses study-space usage and reservations rather than catalog management.',
                ],
                [
                    'title' => 'Library Collection Acquisition and Processing Tracker',
                    'abstract' => 'This project tracks requested, acquired, cataloged, and shelved library materials. Staff can monitor processing stages, record supplier details, and review turnaround summaries for new collection items.',
                    'reason' => 'Focuses on the workflow for preparing new materials for circulation.',
                ],
                [
                    'title' => 'Library Borrowing Pattern and Collection Usage Dashboard',
                    'abstract' => 'This project summarizes borrowing activity by material type, period, and collection category. Library staff can use filtered reports to identify heavily used and underused resources when planning collection improvements.',
                    'reason' => 'Shifts the focus to collection-use analysis and evidence-based planning.',
                ],
            ];
        }

        if (
            str_contains($text, 'security')
            || str_contains($text, 'cybersecurity')
            || str_contains($text, 'incident response')
            || str_contains($text, 'access control')
        ) {
            return [
                [
                    'title' => 'Security Incident Reporting and Response Tracking Portal',
                    'abstract' => 'This project provides an internal portal for reporting security incidents, assigning response owners, and recording resolution steps. Authorized personnel can track incident status and review trend summaries while maintaining a clear activity history.',
                    'reason' => 'Emphasizes incident handling and accountability rather than passive monitoring.',
                ],
                [
                    'title' => 'Role-Based Access Review and Account Audit System',
                    'abstract' => 'This project helps administrators review user accounts, assigned roles, and access change history. It flags accounts due for review and produces audit reports to support regular access cleanup.',
                    'reason' => 'Focuses on access governance and account review as a manageable security scope.',
                ],
                [
                    'title' => 'Phishing Awareness Training and Simulation Tracker',
                    'abstract' => 'This project organizes security awareness lessons, tracks participation, and records results from approved phishing simulations. Administrators can review aggregate outcomes and schedule follow-up learning activities.',
                    'reason' => 'Targets user awareness and training evaluation instead of network intrusion detection.',
                ],
            ];
        }

        if (
            str_contains($text, 'e-commerce')
            || str_contains($text, 'ecommerce')
            || str_contains($text, 'online shop')
            || str_contains($text, 'online store')
            || str_contains($text, 'order fulfillment')
        ) {
            return [
                [
                    'title' => 'Online Order Fulfillment and Delivery Status Portal',
                    'abstract' => 'This project tracks online orders from confirmation through packing and delivery handoff. Staff can update fulfillment stages, customers can review order progress, and managers can view delay and completion summaries.',
                    'reason' => 'Concentrates on order fulfillment visibility after purchase.',
                ],
                [
                    'title' => 'Small Business Product Catalog and Promotion Scheduling System',
                    'abstract' => 'This project lets a small business maintain product listings, schedule promotions, and track changes to prices and availability. Reports summarize active promotions and catalog updates for staff review.',
                    'reason' => 'Focuses on catalog operations and promotion planning rather than payment processing.',
                ],
                [
                    'title' => 'Customer Return and Exchange Request Management Portal',
                    'abstract' => 'This project organizes return and exchange requests, supporting documents, review decisions, and resolution status. Staff can identify overdue requests and summarize common reasons for returns.',
                    'reason' => 'Addresses post-purchase support as a distinct e-commerce workflow.',
                ],
            ];
        }

        if (
            str_contains($text, 'transport')
            || str_contains($text, 'commute')
            || str_contains($text, 'vehicle')
            || str_contains($text, 'fleet')
            || str_contains($text, 'parking')
        ) {
            return [
                [
                    'title' => 'Fleet Maintenance Scheduling and Service History System',
                    'abstract' => 'This project stores vehicle profiles, maintenance schedules, repair records, and service costs. It alerts authorized staff about upcoming maintenance and provides fleet availability and expense summaries.',
                    'reason' => 'Focuses on vehicle readiness and maintenance history rather than route optimization.',
                ],
                [
                    'title' => 'Campus Shuttle Schedule and Passenger Feedback Platform',
                    'abstract' => 'This project publishes shuttle schedules and service notices, and collects passenger feedback about routes and trips. Coordinators can review reported issues and summarize service demand by route and time.',
                    'reason' => 'Targets shuttle information and service feedback for a defined campus setting.',
                ],
                [
                    'title' => 'Parking Slot Reservation and Violation Recording System',
                    'abstract' => 'This project manages reservable parking spaces, records vehicle entry and exit, and documents parking violations for authorized staff. Occupancy and usage reports help administrators review parking demand.',
                    'reason' => 'Narrows the transportation problem to parking allocation and usage records.',
                ],
            ];
        }

        if (
            str_contains($text, 'waste')
            || str_contains($text, 'recycling')
            || str_contains($text, 'environment')
            || str_contains($text, 'pollution')
        ) {
            return [
                [
                    'title' => 'Community Waste Collection Schedule and Pickup Reporting System',
                    'abstract' => 'This project publishes waste collection schedules and lets residents report missed pickups or overflowing collection points. Coordinators can assign follow-ups and review service completion by area.',
                    'reason' => 'Focuses on collection coordination and issue follow-up rather than environmental sensing.',
                ],
                [
                    'title' => 'Recycling Drop-Off Locator and Materials Tracking Platform',
                    'abstract' => 'This project maintains recycling drop-off locations, accepted material types, and reported collection volumes. Users can find nearby facilities while coordinators review participation and collection summaries.',
                    'reason' => 'Adds location discovery and recycling participation records as a practical community service.',
                ],
                [
                    'title' => 'Campus Energy and Water Consumption Monitoring Dashboard',
                    'abstract' => 'This project records periodic utility readings by building and presents consumption trends for authorized facilities staff. It highlights unusual changes for manual review and supports comparisons across reporting periods.',
                    'reason' => 'Provides a measurable sustainability focus through utility tracking and trend review.',
                ],
            ];
        }

        if (
            str_contains($text, 'finance')
            || str_contains($text, 'budget')
            || str_contains($text, 'expense')
            || str_contains($text, 'accounting')
            || str_contains($text, 'payroll')
        ) {
            return [
                [
                    'title' => 'Department Budget Request and Allocation Monitoring System',
                    'abstract' => 'This project records budget requests, review decisions, allocations, and spending updates by department. It provides approval history and budget-versus-actual summaries for authorized finance staff.',
                    'reason' => 'Focuses on internal budget planning and traceable approvals rather than general accounting.',
                ],
                [
                    'title' => 'Small Business Expense Submission and Reimbursement Tracker',
                    'abstract' => 'This project lets employees submit expense claims with supporting documents and track review and reimbursement status. Finance staff can categorize claims and generate summaries by period and expense type.',
                    'reason' => 'Targets a specific finance workflow with submission, review, and status tracking.',
                ],
                [
                    'title' => 'Payroll Attendance Reconciliation and Exception Review System',
                    'abstract' => 'This project compares approved attendance records with payroll periods and lists discrepancies for authorized staff review. It keeps adjustments and approvals traceable and produces reconciliation summaries.',
                    'reason' => 'Emphasizes reconciliation and exception handling rather than calculating payroll automatically.',
                ],
            ];
        }

        if (
            str_contains($text, 'disaster')
            || str_contains($text, 'emergency')
            || str_contains($text, 'evacuation')
            || str_contains($text, 'relief')
        ) {
            return [
                [
                    'title' => 'Community Emergency Resource and Evacuation Information Portal',
                    'abstract' => 'This project centralizes verified evacuation sites, emergency contacts, and preparedness notices for a defined community. Administrators can update information and residents can access current guidance and location details.',
                    'reason' => 'Focuses on access to preparedness information rather than predicting or managing emergencies autonomously.',
                ],
                [
                    'title' => 'Relief Goods Inventory and Distribution Tracking System',
                    'abstract' => 'This project records donated supplies, stock levels, distribution schedules, and recipient acknowledgments during relief operations. Coordinators can monitor remaining goods and review distribution records by location.',
                    'reason' => 'Targets transparent relief inventory and distribution coordination.',
                ],
                [
                    'title' => 'Incident Report and Volunteer Task Coordination Platform',
                    'abstract' => 'This project logs community incident reports and coordinates volunteer tasks with assigned locations and status updates. Coordinators can prioritize reports and view unresolved work during response activities.',
                    'reason' => 'Emphasizes coordination and status tracking for responders and volunteers.',
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
