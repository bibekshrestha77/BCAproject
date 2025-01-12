<?php
session_start();
include '../config.php';
require '../vendor/autoload.php'; // Make sure you have PhpSpreadsheet installed via composer

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Check if user is admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Check if election ID is provided
if(!isset($_GET['election_id'])) {
    die("Election ID not provided");
}

$election_id = mysqli_real_escape_string($conn, $_GET['election_id']);

// Get election details
$election_query = "SELECT * FROM elections WHERE id = $election_id";
$election_result = mysqli_query($conn, $election_query);
$election = mysqli_fetch_assoc($election_result);

if(!$election) {
    die("Election not found");
}

// Get candidates and their votes
$candidates_query = "SELECT c.*, 
                           COUNT(v.id) as vote_count,
                           (COUNT(v.id) / (
                               SELECT COUNT(*) 
                               FROM votes 
                               WHERE election_id = $election_id
                           )) * 100 as vote_percentage
                    FROM candidates c
                    LEFT JOIN votes v ON c.id = v.candidate_id
                    WHERE c.election_id = $election_id
                    GROUP BY c.id
                    ORDER BY vote_count DESC";
$candidates_result = mysqli_query($conn, $candidates_query);

// Create new spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set document properties
$spreadsheet->getProperties()
    ->setCreator("Voting System")
    ->setLastModifiedBy("Admin")
    ->setTitle("Election Results - " . $election['title'])
    ->setSubject("Election Results Report")
    ->setDescription("Detailed results for " . $election['title']);

// Style header
$sheet->getStyle('A1:E1')->applyFromArray([
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF'],
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '4CAF50'],
    ],
]);

// Set headers
$sheet->setCellValue('A1', 'Candidate Name');
$sheet->setCellValue('B1', 'Position');
$sheet->setCellValue('C1', 'Votes');
$sheet->setCellValue('D1', 'Percentage');
$sheet->setCellValue('E1', 'Rank');

// Add data
$row = 2;
$rank = 1;
while($candidate = mysqli_fetch_assoc($candidates_result)) {
    $sheet->setCellValue('A' . $row, $candidate['name']);
    $sheet->setCellValue('B' . $row, $candidate['position']);
    $sheet->setCellValue('C' . $row, $candidate['vote_count']);
    $sheet->setCellValue('D' . $row, number_format($candidate['vote_percentage'], 2) . '%');
    $sheet->setCellValue('E' . $row, $rank);
    
    // Style percentage cell
    $sheet->getStyle('D' . $row)->getNumberFormat()
        ->setFormatCode('0.00"%"');
    
    $row++;
    $rank++;
}

// Add election information
$row += 2;
$sheet->setCellValue('A' . $row, 'Election Information');
$sheet->mergeCells('A' . $row . ':E' . $row);
$sheet->getStyle('A' . $row)->applyFromArray([
    'font' => ['bold' => true],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'E8F5E9'],
    ],
]);

$row++;
$sheet->setCellValue('A' . $row, 'Title:');
$sheet->setCellValue('B' . $row, $election['title']);
$sheet->mergeCells('B' . $row . ':E' . $row);

$row++;
$sheet->setCellValue('A' . $row, 'Start Date:');
$sheet->setCellValue('B' . $row, date('F d, Y H:i', strtotime($election['start_date'])));
$sheet->mergeCells('B' . $row . ':E' . $row);

$row++;
$sheet->setCellValue('A' . $row, 'End Date:');
$sheet->setCellValue('B' . $row, date('F d, Y H:i', strtotime($election['end_date'])));
$sheet->mergeCells('B' . $row . ':E' . $row);

$row++;
$sheet->setCellValue('A' . $row, 'Status:');
$sheet->setCellValue('B' . $row, ucfirst($election['status']));
$sheet->mergeCells('B' . $row . ':E' . $row);

// Auto-size columns
foreach(range('A','E') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Create writer
$writer = new Xlsx($spreadsheet);

// Set headers for download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="election_results_' . $election_id . '.xlsx"');
header('Cache-Control: max-age=0');

// Save file to PHP output
$writer->save('php://output');
exit; 