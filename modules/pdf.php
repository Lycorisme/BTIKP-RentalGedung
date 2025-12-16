<?php
// modules/pdf.php
// PDF Generation with Dompdf

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Generate PDF with custom HTML
function generate_pdf($html, $filename = 'document.pdf', $orientation = 'portrait') {
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'DejaVu Sans');
    
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', $orientation);
    $dompdf->render();
    
    $dompdf->stream($filename, ["Attachment" => false]);
}

// Generate PDF with kop surat template
function generate_pdf_with_kop($content, $title, $filename = 'laporan.pdf') {
    $settings = get_all_settings();
    
    // Load logo as base64
    $logoBase64 = '';
    if (!empty($settings['logo_url'])) {
        $logoPath = __DIR__ . '/../' . $settings['logo_url'];
        if (file_exists($logoPath)) {
            $type = pathinfo($logoPath, PATHINFO_EXTENSION);
            $data = file_get_contents($logoPath);
            $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
    }
    
    // Load TTD as base64
    $ttdBase64 = '';
    if (!empty($settings['ttd_image'])) {
        $ttdPath = __DIR__ . '/../' . $settings['ttd_image'];
        if (file_exists($ttdPath)) {
            $type = pathinfo($ttdPath, PATHINFO_EXTENSION);
            $data = file_get_contents($ttdPath);
            $ttdBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
    }
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11pt; }
            .kop-surat {
                text-align: center;
                border-bottom: 3px double #000;
                padding-bottom: 10px;
                margin-bottom: 20px;
            }
            .kop-surat table { width: 100%; border: none; border-collapse: collapse; }
            .kop-surat td { border: none; }
            .kop-surat .logo-cell { width: 100px; text-align: center; vertical-align: middle; }
            .kop-surat .text-cell { text-align: center; vertical-align: middle; }
            .kop-surat img { max-height: 80px; max-width: 80px; }
            .kop-surat h2 { margin: 0; font-size: 16pt; font-weight: bold; text-transform: uppercase; }
            .kop-surat p { margin: 2px 0; font-size: 10pt; }
            .title { text-align: center; margin: 20px 0; }
            .title h3 { margin: 0; text-decoration: underline; font-size: 14pt; }
            .content { margin: 20px 0; }
            .ttd-box { float: right; width: 40%; text-align: center; margin-top: 40px; }
            .ttd-image { height: 80px; margin: 10px auto; }
            .ttd-spacer { height: 80px; }
            .ttd-nama { font-weight: bold; text-decoration: underline; }
        </style>
    </head>
    <body>
        <div class="kop-surat">
            <table>
                <tr>
                    <td class="logo-cell">' . 
                        (!empty($logoBase64) ? '<img src="' . $logoBase64 . '" alt="Logo">' : '') . 
                    '</td>
                    <td class="text-cell">
                        <h2>' . htmlspecialchars($settings['instansi_nama'] ?? 'PT. Rental Gedung') . '</h2>
                        <p>' . nl2br(htmlspecialchars($settings['instansi_alamat'] ?? '')) . '</p>
                        <p>Telp: ' . htmlspecialchars($settings['instansi_telepon'] ?? '-') . ' | Email: ' . htmlspecialchars($settings['instansi_email'] ?? '-') . '</p>
                    </td>
                    <td class="logo-cell"></td>
                </tr>
            </table>
        </div>
        
        <div class="title">
            <h3>' . htmlspecialchars($title) . '</h3>
            <p>Tanggal: ' . date('d F Y') . '</p>
        </div>
        
        <div class="content">
            ' . $content . '
        </div>
        
        <div class="ttd-box">
            <p>' . htmlspecialchars($settings['instansi_alamat'] ?? 'Kota') . ', ' . date('d F Y') . '</p>
            <p>' . htmlspecialchars($settings['ttd_jabatan'] ?? 'Pimpinan') . '</p>
            ' . (!empty($ttdBase64) ? '<img src="' . $ttdBase64 . '" class="ttd-image" alt="TTD">' : '<div class="ttd-spacer"></div>') . '
            <div class="ttd-nama">' . htmlspecialchars($settings['ttd_nama'] ?? '-') . '</div>
            ' . (!empty($settings['ttd_nip']) ? '<div>NIP. ' . htmlspecialchars($settings['ttd_nip']) . '</div>' : '') . '
        </div>
    </body>
    </html>
    ';
    
    generate_pdf($html, $filename);
}
?>