<?php
class AutoEscalator
{
    public static function runEscalation($conn)
    {
        $conn->query("
            UPDATE complaints c
            JOIN complaint_statuses s ON c.status_id = s.status_id
            SET c.assigned_role = 'Coordinator'
            WHERE c.assigned_role = 'DeptAdmin' 
            AND s.status_label = 'Pending'
            AND DATEDIFF(NOW(), c.updated_at) >= 2
        ");

        $conn->query("
            UPDATE complaints c
            JOIN complaint_statuses s ON c.status_id = s.status_id
            SET c.assigned_role = 'HOD'
            WHERE c.assigned_role = 'Coordinator' 
            AND s.status_label = 'Pending'
            AND DATEDIFF(NOW(), c.updated_at) >= 1
        ");

        $conn->query("
            UPDATE complaints c
            JOIN complaint_statuses s ON c.status_id = s.status_id
            SET c.assigned_role = 'Dean'
            WHERE c.assigned_role = 'HOD' 
            AND s.status_label = 'Pending'
            AND DATEDIFF(NOW(), c.updated_at) >= 10
        ");
    }
}
?>
