/**
 * Client-side PDF Report Generator for Recashly
 * Uses jsPDF to generate reimbursement report PDFs directly in the browser.
 * This bypasses the server-side DomPDF dependency on PHP GD extension.
 */
(function () {
    'use strict';

    // Color constants matching the existing PDF template
    const COLORS = {
        primary: [0, 188, 212],      // #00bcd4 - cyan
        primaryDark: [0, 131, 143],   // #00838f - dark cyan
        white: [255, 255, 255],
        black: [51, 51, 51],          // #333
        gray: [85, 85, 85],           // #555
        lightGray: [136, 136, 136],   // #888
        tableBorder: [221, 221, 221], // #ddd
        bgLight: [245, 245, 245],     // #f5f5f5
        bgCyan: [224, 247, 250],      // #e0f7fa
        evenRow: [249, 249, 249],     // #f9f9f9
    };

    /**
     * Load an image as base64 data URL, with CORS handling via proxy.
     */
    async function loadImageAsBase64(url) {
        if (!url) return null;

        try {
            const response = await fetch(url, { mode: 'cors' });
            if (!response.ok) return null;
            const blob = await response.blob();
            return new Promise((resolve) => {
                const reader = new FileReader();
                reader.onload = () => resolve(reader.result);
                reader.onerror = () => resolve(null);
                reader.readAsDataURL(blob);
            });
        } catch (e) {
            // If CORS fails, try loading via img element
            try {
                return await new Promise((resolve) => {
                    const img = new Image();
                    img.crossOrigin = 'anonymous';
                    img.onload = () => {
                        const canvas = document.createElement('canvas');
                        canvas.width = img.naturalWidth;
                        canvas.height = img.naturalHeight;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0);
                        try {
                            resolve(canvas.toDataURL('image/jpeg', 0.8));
                        } catch (canvasErr) {
                            resolve(null);
                        }
                    };
                    img.onerror = () => resolve(null);
                    img.src = url;
                });
            } catch (e2) {
                return null;
            }
        }
    }

    /**
     * Generate PDF from report data.
     * @param {Object} data - Report data from API
     * @returns {jsPDF} - The generated PDF document
     */
    async function generatePdf(data, progressCallback) {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('p', 'mm', 'a4');
        const pageWidth = 210;
        const pageHeight = 297;
        const margin = 15;
        const contentWidth = pageWidth - (margin * 2);

        const report = data.report;
        const user = data.user;
        const reimbursements = data.reimbursements;

        // ============ PAGE 1: DATA TABLE ============
        let y = margin;

        // --- Header ---
        doc.setFillColor(...COLORS.primary);
        doc.rect(margin, y, contentWidth, 0.8, 'F');

        y += 5;
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(16);
        doc.setTextColor(...COLORS.primaryDark);
        doc.text('LAPORAN REIMBURSEMENT', pageWidth / 2, y + 5, { align: 'center' });

        y += 10;
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(11);
        doc.setTextColor(...COLORS.gray);
        doc.text(`Periode: ${report.period_start} - ${report.period_end}`, pageWidth / 2, y + 3, { align: 'center' });

        y += 8;
        doc.setFillColor(...COLORS.primary);
        doc.rect(margin, y, contentWidth, 0.8, 'F');

        y += 6;

        // --- User Info Box ---
        doc.setFillColor(...COLORS.bgLight);
        doc.roundedRect(margin, y, contentWidth, 22, 2, 2, 'F');

        const infoY = y + 6;
        doc.setFontSize(9);
        doc.setTextColor(...COLORS.black);

        doc.setFont('helvetica', 'bold');
        doc.text('Nama:', margin + 5, infoY);
        doc.setFont('helvetica', 'normal');
        doc.text(user.name, margin + 30, infoY);

        doc.setFont('helvetica', 'bold');
        doc.text('Tanggal Cetak:', contentWidth / 2 + margin, infoY);
        doc.setFont('helvetica', 'normal');
        doc.text(data.generated_at, contentWidth / 2 + margin + 32, infoY);

        doc.setFont('helvetica', 'bold');
        doc.text('Email:', margin + 5, infoY + 8);
        doc.setFont('helvetica', 'normal');
        doc.text(user.email, margin + 30, infoY + 8);

        doc.setFont('helvetica', 'bold');
        doc.text('Status:', contentWidth / 2 + margin, infoY + 8);
        doc.setFont('helvetica', 'normal');
        doc.text(report.status, contentWidth / 2 + margin + 32, infoY + 8);

        y += 28;

        // --- Data Table ---
        const columns = [
            { header: 'NO', width: 10, align: 'center' },
            { header: 'TANGGAL', width: 22, align: 'center' },
            { header: 'CLIENT', width: 35, align: 'left' },
            { header: 'KATEGORI', width: 28, align: 'left' },
            { header: 'KETERANGAN', width: 50, align: 'left' },
            { header: 'JUMLAH', width: 35, align: 'right' },
        ];

        const rowHeight = 7;
        const headerHeight = 8;

        // Table header
        doc.setFillColor(...COLORS.primary);
        doc.rect(margin, y, contentWidth, headerHeight, 'F');
        doc.setTextColor(...COLORS.white);
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(8);

        let colX = margin;
        columns.forEach(col => {
            let textX = colX + 2;
            if (col.align === 'center') textX = colX + col.width / 2;
            if (col.align === 'right') textX = colX + col.width - 2;
            doc.text(col.header, textX, y + 5.5, { align: col.align === 'left' ? undefined : col.align });
            colX += col.width;
        });

        y += headerHeight;

        // Table rows
        doc.setFontSize(8);
        reimbursements.forEach((item, index) => {
            // Check if we need a new page
            if (y + rowHeight > pageHeight - 50) {
                doc.addPage();
                y = margin;
            }

            // Alternate row background
            if (index % 2 === 0) {
                doc.setFillColor(...COLORS.evenRow);
                doc.rect(margin, y, contentWidth, rowHeight, 'F');
            }

            // Row border
            doc.setDrawColor(...COLORS.tableBorder);
            doc.rect(margin, y, contentWidth, rowHeight, 'S');

            doc.setTextColor(...COLORS.black);
            doc.setFont('helvetica', 'normal');

            const rowData = [
                String(index + 1),
                item.transaction_date,
                item.client_name,
                item.category_name,
                item.note,
                item.amount_formatted,
            ];

            colX = margin;
            columns.forEach((col, colIndex) => {
                let textX = colX + 2;
                if (col.align === 'center') textX = colX + col.width / 2;
                if (col.align === 'right') textX = colX + col.width - 2;

                let text = rowData[colIndex];
                // Truncate long text
                const maxChars = Math.floor(col.width / 1.8);
                if (text.length > maxChars) {
                    text = text.substring(0, maxChars - 1) + '…';
                }

                doc.text(text, textX, y + 4.8, { align: col.align === 'left' ? undefined : col.align });
                colX += col.width;
            });

            y += rowHeight;
        });

        y += 8;

        // --- Summary Box ---
        if (y + 20 > pageHeight - 30) {
            doc.addPage();
            y = margin;
        }

        doc.setFillColor(...COLORS.bgCyan);
        doc.roundedRect(margin, y, contentWidth, 18, 2, 2, 'F');

        doc.setTextColor(...COLORS.black);
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(11);
        doc.text('Total Entry:', margin + 5, y + 8);
        doc.setFont('helvetica', 'normal');
        doc.text(`${report.entry_count} item`, margin + 35, y + 8);

        doc.setFont('helvetica', 'bold');
        doc.text('Total Jumlah:', contentWidth / 2 + margin, y + 8);
        doc.setFontSize(13);
        doc.setTextColor(...COLORS.primaryDark);
        doc.text(report.total_amount_formatted, pageWidth - margin - 5, y + 8, { align: 'right' });

        // --- Footer ---
        doc.setFontSize(7);
        doc.setTextColor(...COLORS.lightGray);
        doc.setDrawColor(...COLORS.tableBorder);
        doc.line(margin, pageHeight - 15, pageWidth - margin, pageHeight - 15);
        doc.text(`Generated by Reimburse System | ${data.app_name}`, pageWidth / 2, pageHeight - 10, { align: 'center' });

        // ============ PAGE 2+: RECEIPT IMAGES ============
        if (reimbursements.length > 0) {
            const imagesPerPage = 4;
            const totalImagePages = Math.ceil(reimbursements.length / imagesPerPage);

            for (let pageIdx = 0; pageIdx < totalImagePages; pageIdx++) {
                doc.addPage();
                y = margin;

                // Page title
                doc.setFillColor(...COLORS.primary);
                doc.rect(margin, y + 10, contentWidth, 0.5, 'F');
                doc.setFont('helvetica', 'bold');
                doc.setFontSize(12);
                doc.setTextColor(...COLORS.primaryDark);
                doc.text(
                    `Bukti Transaksi (Halaman ${pageIdx + 1} dari ${totalImagePages})`,
                    pageWidth / 2, y + 7, { align: 'center' }
                );

                y += 16;

                const startIdx = pageIdx * imagesPerPage;
                const endIdx = Math.min(startIdx + imagesPerPage, reimbursements.length);
                const pageItems = reimbursements.slice(startIdx, endIdx);

                // 2x2 grid
                const cellWidth = (contentWidth - 6) / 2;
                const cellHeight = 115;

                for (let i = 0; i < pageItems.length; i++) {
                    const item = pageItems[i];
                    const col = i % 2;
                    const row = Math.floor(i / 2);
                    const cellX = margin + col * (cellWidth + 6);
                    const cellY = y + row * (cellHeight + 6);

                    // Cell border
                    doc.setDrawColor(...COLORS.tableBorder);
                    doc.roundedRect(cellX, cellY, cellWidth, cellHeight, 2, 2, 'S');

                    // Try loading image
                    if (progressCallback) {
                        progressCallback(`Loading image ${startIdx + i + 1}/${reimbursements.length}...`);
                    }

                    const imageData = item.image_url ? await loadImageAsBase64(item.image_url) : null;

                    if (imageData) {
                        try {
                            // Calculate image dimensions to fit within cell
                            const imgMaxWidth = cellWidth - 8;
                            const imgMaxHeight = cellHeight - 30;
                            doc.addImage(imageData, 'JPEG', cellX + 4, cellY + 4, imgMaxWidth, imgMaxHeight, undefined, 'FAST');
                        } catch (imgErr) {
                            // Fallback: show placeholder
                            doc.setFillColor(...COLORS.bgLight);
                            doc.rect(cellX + 4, cellY + 4, cellWidth - 8, cellHeight - 30, 'F');
                            doc.setTextColor(...COLORS.lightGray);
                            doc.setFontSize(9);
                            doc.text('Image not available', cellX + cellWidth / 2, cellY + (cellHeight - 30) / 2 + 4, { align: 'center' });
                        }
                    } else {
                        // No image placeholder
                        doc.setFillColor(...COLORS.bgLight);
                        doc.rect(cellX + 4, cellY + 4, cellWidth - 8, cellHeight - 30, 'F');
                        doc.setTextColor(...COLORS.lightGray);
                        doc.setFontSize(9);
                        doc.text('Image not available', cellX + cellWidth / 2, cellY + (cellHeight - 30) / 2 + 4, { align: 'center' });
                    }

                    // Image info below
                    const infoBoxY = cellY + cellHeight - 24;
                    doc.setDrawColor(...COLORS.evenRow);
                    doc.line(cellX + 4, infoBoxY, cellX + cellWidth - 4, infoBoxY);

                    doc.setTextColor(...COLORS.black);
                    doc.setFont('helvetica', 'bold');
                    doc.setFontSize(8);
                    doc.text(`${item.transaction_date} - ${item.client_name}`, cellX + 5, infoBoxY + 5);

                    doc.setFont('helvetica', 'normal');
                    doc.setFontSize(7);
                    doc.text(`${item.category_name}: ${item.amount_formatted}`, cellX + 5, infoBoxY + 11);
                }

                // Footer
                doc.setFontSize(7);
                doc.setTextColor(...COLORS.lightGray);
                doc.setDrawColor(...COLORS.tableBorder);
                doc.line(margin, pageHeight - 15, pageWidth - margin, pageHeight - 15);
                doc.text(`Generated by Reimburse System | ${data.app_name}`, pageWidth / 2, pageHeight - 10, { align: 'center' });
            }
        }

        return doc;
    }

    /**
     * Main entry point: fetch data and generate PDF.
     * Called from Filament action via Alpine.js / Livewire event.
     */
    window.RecashlyPdfGenerator = {
        async generate(reportId) {
            try {
                // Show loading notification via Filament
                if (window.Livewire) {
                    window.Livewire.dispatch('pdf-generating');
                }

                // Fetch report data
                const response = await fetch(`/admin/reports/${reportId}/pdf-data`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    throw new Error(`Failed to fetch report data (HTTP ${response.status})`);
                }

                const result = await response.json();
                if (!result.success) {
                    throw new Error(result.error || 'Unknown error');
                }

                // Generate PDF
                const doc = await generatePdf(result.data, (msg) => {
                    console.log('[PDF Generator]', msg);
                });

                // Download the PDF
                const filename = `Reimburse_${result.data.report.period_label.replace(/\s+/g, '_')}.pdf`;
                doc.save(filename);

                // Notify Livewire of success
                if (window.Livewire) {
                    window.Livewire.dispatch('pdf-generated', { reportId: reportId });
                }

                return true;
            } catch (error) {
                console.error('[PDF Generator] Error:', error);

                // Notify Livewire of failure
                if (window.Livewire) {
                    window.Livewire.dispatch('pdf-generation-failed', { message: error.message });
                }

                return false;
            }
        }
    };

    // Listen for Livewire dispatch events from Filament actions
    document.addEventListener('DOMContentLoaded', function () {
        if (window.Livewire) {
            window.Livewire.on('trigger-pdf-generation', (params) => {
                const reportId = Array.isArray(params) ? params[0]?.reportId : params?.reportId;
                if (reportId) {
                    window.RecashlyPdfGenerator.generate(reportId);
                }
            });
        }
    });
})();
