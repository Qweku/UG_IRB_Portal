<?php
require_once '../../includes/functions/helpers.php';
header('Content-Type: application/json');

$chair_person = '';
$preparer = '';
$preparer_title = '';
$preparer = '';
$guests = '';
$education = '';
$agenda_time = '';
$location = '';
$agenda_heading = '';
$old_business = '';
$new_business = '';
$additional_heading = '';
$additional_remarks = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $fields = ['chair_person', 'preparer', 'preparer_title', 'guests', 'education', 'agenda_time', 'location', 'agenda_heading', 'old_business', 'new_business', 'additional_heading', 'additional_remarks'];

    foreach($fields as $field){
        $data[$field] = trim($_POST[$field] ?? '');
    }

    try {
        $db = new Database();
        $conn = $db->connect();

        if (!$conn) {
            throw new Exception("Database connection failed");
        }

        $conn->beginTransaction();

        $nextMeeting = getNextMeetingDate();
        
         $stmt = $conn->prepare("INSERT INTO agenda_records (chair_person, meeting_date, preparer, preparer_title, guests, 
         education, agenda_time, agenda_location, agenda_heading, old_business, new_business, additional_heading, additional_remarks)
         VALUES (:chair_person, :meeting_date, :preparer, :preparer_title, :guests, 
         :education, :agenda_time, :agenda_location, :agenda_heading, :old_business, :new_business, :additional_heading, :additional_remarks) ");

         $stmt->execute([
                   ':chair_person' => $data['chair_person'],
                   ':meeting_date' => $nextMeeting,
                    ':preparer' => $data['preparer'],
                    ':preparer_title' => $data['preparer_title'],
                    ':guests' => $data['guests'],
                    ':education' => $data['education'],
                    ':agenda_time' => $data['agenda_time'],
                    ':agenda_location' => $data['location'],
                    ':agenda_heading' => $data['agenda_heading'],
                    ':old_business' => $data['old_business'],
                    ':new_business' => $data['new_business'],
                    ':additional_heading' => $data['additional_heading'],
                    ':additional_remarks' => $data['additional_remarks'],
                    
                ]);

                $conn->commit();
        $message = 'New Agenda Record have been saved successfully!';
        echo json_encode(['status' => 'success', 'message' => $message]);


    }catch(PDOException $e){
        $conn->rollBack();
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Failed to save agenda. Please try again.']);
    }
}
