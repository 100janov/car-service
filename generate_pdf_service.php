<?php
// Include TCPDF library
require_once('tcpdf/tcpdf.php');
require_once('../config.php');

// Create a new TCPDF instance
$pdf = new TCPDF();

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('dejavusans', '', 12); // Use 'dejavusans' font, which supports Cyrillic characters

// Set font encoding
$pdf->SetFont('', '', 12, '', true); // Ensure Unicode encoding

// Insert logo
$pdf->Image('images/logo_black.png', 85, 10, 40, 0, 'PNG'); // Adjust the path and dimensions as needed

// Set content
$pdf->Cell(0, 10, 'Top Car Service - Сервисен извештај', 0, 1, 'C');



// Fetch data for the specific record from the database
$carId = isset($_GET['car_id']) ? intval($_GET['car_id']) : 0;
$serviceId = isset($_GET['service_id']) ? intval($_GET['service_id']) : 0;


if ($carId > 0) {
    // Fetch car details
   
    $carStmt = $conn->prepare("SELECT c.make, c.model, c.registration_plate, c.year, c.engine, c.owner_name, s.current_mileage FROM cars AS c INNER JOIN services AS s ON c.car_id = s.car_id WHERE c.car_id = ? AND s.service_id = ?");
    $carStmt->bind_param("ii", $carId, $serviceId);
    $carStmt->execute();
    $carResult = $carStmt->get_result();
    $car = $carResult->fetch_assoc();
    $carStmt->close();

    // Fetch work details for the specific car
    $workStmt = $conn->prepare("SELECT work_done, amount_due, is_paid FROM work_details WHERE car_id = ? AND service_id = ?");
    $workStmt->bind_param("ii", $carId, $serviceId);
    $workStmt->execute();
    $workResult = $workStmt->get_result();
    

    // Display car details in the PDF
    $pdf->Cell(0, 10, "", 0, 1, 'L');
    $pdf->Cell(0, 10, "", 0, 1, 'L');
    $pdf->Cell(0, 10, "Контакт: 078589898", 0, 1, 'C');
    $pdf->Cell(0, 10, "───────────────────────────────────────────────────────────────────────────", 0, 1, 'L');

// Display car details in the PDF
$pdf->Cell(0, 10, "Податоци за возилото:", 0, 1, 'C');

$pdf->Cell(90, 10, "Производител: {$car['make']}", 0, 0, 'L');
$pdf->Cell(90, 10, "Модел: {$car['model']}", 0, 1, 'L');


$pdf->Cell(90, 10, "Мотор: {$car['engine']}", 0, 0, 'L');

$pdf->Cell(90, 10, "Година: {$car['year']}", 0, 1, 'L');


$pdf->Cell(90, 10, "Регистарска табличка: {$car['registration_plate']}", 0, 0, 'L');
$pdf->Cell(90, 10, "Моментална километража: {$car['current_mileage']}", 0, 1, 'L');

$pdf->Cell(90, 10, "Сопственик: {$car['owner_name']}", 0, 1, 'L');


    
    $pdf->Cell(0, 10, "───────────────────────────────────────────────────────────────────────────", 0, 1, 'L');
    // Display work details in a two-column table
    $pdf->Cell(0, 10, "Сервис опис:", 0, 1, 'C');
    $pdf->Ln(5); // Add some space

    $pdf->SetFont('dejavusans', '', 10); // Reduce font size for work details

    $pdf->SetFillColor(200, 200, 200); // Set background color for table header
    $pdf->Cell(60, 10, "Сработено", 1, 0, 'C', true); // Cell for work done
    $pdf->Cell(60, 10, "Сума (денари)", 1, 0, 'C', true); // Cell for amount due
    $pdf->Cell(60, 10, "Статус Плаќање", 1, 1, 'C', true); // Cell for additional column

    $pdf->SetFont('dejavusans', '', 12); // Restore font size

    // Display work details in the PDF
    while ($workDetails = $workResult->fetch_assoc()) {
        // Display work done and amount due in two separate cells
        $pdf->Cell(60, 10, $workDetails['work_done'], 1, 0, 'L');
        $pdf->Cell(60, 10, $workDetails['amount_due'], 1, 0, 'R'); // Align amount due to the right
        $pdf->Cell(60, 10, $workDetails['is_paid'] == 1 ? 'Платено' : 'Неплатено', 1, 1, 'R');
    }

    $pdf->Ln(5); // Add some space after the table

    // Calculate and display total amount
    $totalAmountStmt = $conn->prepare("SELECT SUM(amount_due) AS total_amount FROM work_details WHERE car_id = ? AND service_id = ?");
    $totalAmountStmt->bind_param("ii", $carId, $serviceId);
    $totalAmountStmt->execute();
    $totalAmountResult = $totalAmountStmt->get_result();
    $totalAmount = $totalAmountResult->fetch_assoc()['total_amount'];
    $totalAmountStmt->close();
    
    
    
    // Fetch work details for the specific car with is_paid = 1
    $paidAmountStmt = $conn->prepare("SELECT SUM(amount_due) AS total_amount_due FROM work_details WHERE car_id = ? AND service_id = ? AND is_paid = 1");
    $paidAmountStmt->bind_param("ii", $carId, $serviceId);
    $paidAmountStmt->execute();
    $paidAmountResult = $paidAmountStmt->get_result();
    $paidAmount = $paidAmountResult->fetch_assoc()['total_amount_due'];
    $paidAmountStmt->close();
    
    $pdf->Cell(0, 10, "Вкупно: {$totalAmount} денари", 0, 0, 'L');
    $pdf->Cell(0, 10, "Платено: {$paidAmount} денари", 0, 1, 'R');
    
    
    
    
    $workStmt->close();
} else {
    $pdf->Cell(0, 10, 'Invalid Car ID', 0, 1, 'L');
}

// Define the PDF filename including the registration plate name
$pdfFilename = 'top_car_servisen_izveshtaj_' . $car['registration_plate'] . '.pdf';

// Output the PDF to the browser and force a download with the modified filename
$pdf->Output($pdfFilename, 'D');

// Close the database connection
$conn->close();
?>
