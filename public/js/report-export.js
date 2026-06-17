/**
 * Unified Report Export Utility for Tickets System (PDF and Excel)
 */
const ReportExportUtil = {
    /**
     * Converts a Hex color string (e.g., "#d4af53") to RGB array [r, g, b].
     */
    hexToRgb(hex) {
        if (!hex) return [212, 175, 83]; // Default fallback
        hex = hex.trim().replace('#', '');
        if (hex.length === 3) {
            hex = hex.split('').map(char => char + char).join('');
        }
        const num = parseInt(hex, 16);
        return [
            (num >> 16) & 255,
            (num >> 8) & 255,
            num & 255
        ];
    },

    /**
     * Retrieves the primary color of the system as an RGB array.
     */
    getSystemPrimaryColorRgb() {
        const primaryColorHex = getComputedStyle(document.documentElement)
            .getPropertyValue('--primary-color')
            .trim() || '#d4af53';
        return this.hexToRgb(primaryColorHex);
    },

    /**
     * Generates and downloads/opens a PDF report.
     * @param {Object} options Configuration options
     */
    async exportPdf(options) {
        const {
            title,
            fetchUrl,
            isAdmin = false,
            onStart = () => { },
            onEnd = () => { }
        } = options;

        onStart();
        try {
            const response = await fetch(fetchUrl);
            const data = await response.json();
            if (!data || !data.success) {
                alert('Failed to fetch report data.');
                return;
            }

            const { jsPDF } = window.jspdf;
            const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });

            const primaryRgb = this.getSystemPrimaryColorRgb();

            // ── Clean Header Layout ──
            const logoImg = document.querySelector('.sidebar-brand-logo');
            let textStartX = 14; // Start X position for text
            if (logoImg) {
                try {
                    // Draw logo on the left (x=14, y=5) with width=12, height=12 mm
                    doc.addImage(logoImg, 'PNG', 14, 5, 12, 12);
                    textStartX = 29; // Shift text to the right of the logo
                } catch (imgError) {
                    console.warn('Failed to add logo to PDF:', imgError);
                }
            }

            // Main Title (using primary color)
            doc.setTextColor(primaryRgb[0], primaryRgb[1], primaryRgb[2]);
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(14);
            doc.text(title, textStartX, 11);

            // Subtitle
            doc.setTextColor(100, 116, 139); // Slate-500 (#64748b)
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(8.5);
            doc.text('Support Tickets Report System', textStartX, 16);

            // Period and Total meta details (on the right)
            doc.setTextColor(71, 85, 105); // Slate-600 (#475569)
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(9);
            const metaText = `Period: ${data.date_from} -> ${data.date_to}         Total: ${data.total} ticket`;
            doc.text(metaText, 297 - 14, 14, { align: 'right' });

            // Thin divider line (using primary color)
            doc.setDrawColor(primaryRgb[0], primaryRgb[1], primaryRgb[2]);
            doc.setLineWidth(0.4);
            doc.line(14, 21, 297 - 14, 21);

            // Table Columns & Data mapping
            let head, body, columnStyles;
            if (isAdmin) {
                head = [['#ID', 'Sender', 'Category', 'Subject', 'Status', 'In Progress By', 'Closed By', 'Date', 'Closed At', 'Resolution Time']];
                body = data.tickets.map(t => [
                    '#' + t.id, t.sender, t.category, t.subject,
                    t.status, t.inprogress_by, t.closed_by, t.created_at, t.solved_at || '---',
                    t.resolution_time || '---'
                ]);
                columnStyles = { 3: { cellWidth: 45 } }; // Subject width
            } else {
                // Agent report (includes Closed At)
                head = [['#ID', 'Subject', 'Category', 'Status', 'Closed By', 'Date', 'Closed At', 'Resolution Time']];
                body = data.tickets.map(t => [
                    '#' + t.id, t.subject, t.category, t.status, t.closed_by, t.created_at, t.solved_at || '---',
                    t.resolution_time || '---'
                ]);
                columnStyles = { 1: { cellWidth: 55 } }; // Subject width
            }

            // Soft tint background (12% opacity) and dark text shade (40% brightness) of primary color
            const headerBg = [
                Math.round(primaryRgb[0] + (255 - primaryRgb[0]) * 0.88),
                Math.round(primaryRgb[1] + (255 - primaryRgb[1]) * 0.88),
                Math.round(primaryRgb[2] + (255 - primaryRgb[2]) * 0.88)
            ];
            const headerText = [
                Math.round(primaryRgb[0] * 0.4),
                Math.round(primaryRgb[1] * 0.4),
                Math.round(primaryRgb[2] * 0.4)
            ];

            doc.autoTable({
                startY: 25,
                head: head,
                body: body,
                headStyles: {
                    fillColor: headerBg,
                    textColor: headerText,
                    fontStyle: 'bold',
                    fontSize: 8.5
                },
                bodyStyles: { fontSize: 7.5 },
                alternateRowStyles: { fillColor: [247, 248, 249] },
                styles: { cellPadding: 2.5, overflow: 'linebreak' },
                columnStyles: columnStyles,
                margin: { left: 14, right: 14 },
            });

            // Open in browser tab
            const pdfBlob = doc.output('blob');
            const blobUrl = URL.createObjectURL(pdfBlob);
            const tab = window.open(blobUrl, '_blank');
            if (!tab) {
                // Popup blocked fallback — force download
                doc.save(`${title.toLowerCase().replace(/\s+/g, '-')}-${data.date_from}-to-${data.date_to}.pdf`);
            }
        } catch (e) {
            console.error(e);
            alert('PDF generation failed.');
        } finally {
            onEnd();
        }
    },

    /**
     * Generates and downloads an Excel report.
     * @param {Object} options Configuration options
     */
    async exportExcel(options) {
        const {
            title,
            fetchUrl,
            isAdmin = false,
            onStart = () => { },
            onEnd = () => { }
        } = options;

        onStart();
        try {
            const response = await fetch(fetchUrl);
            const data = await response.json();
            if (!data || !data.success) {
                alert('Failed to fetch report data.');
                return;
            }

            let rows, cols;
            if (isAdmin) {
                rows = [['#ID', 'Sender', 'Category', 'Subject', 'Status', 'In Progress By', 'Closed By', 'Date', 'Closed At', 'Resolution Time']];
                data.tickets.forEach(t => rows.push([
                    '#' + t.id, t.sender, t.category, t.subject,
                    t.status, t.inprogress_by, t.closed_by, t.created_at, t.solved_at || '---',
                    t.resolution_time || '---'
                ]));
                cols = [8, 20, 14, 40, 12, 20, 20, 20, 20, 18];
            } else {
                rows = [['#ID', 'Subject', 'Category', 'Status', 'Closed By', 'Date', 'Closed At', 'Resolution Time']];
                data.tickets.forEach(t => rows.push([
                    '#' + t.id, t.subject, t.category, t.status, t.closed_by, t.created_at, t.solved_at || '---',
                    t.resolution_time || '---'
                ]));
                cols = [8, 50, 14, 14, 20, 20, 20, 18];
            }

            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.aoa_to_sheet(rows);
            ws['!cols'] = cols.map(w => ({ wch: w }));
            XLSX.utils.book_append_sheet(wb, ws, 'Tickets');
            XLSX.writeFile(wb, `${title.toLowerCase().replace(/\s+/g, '-')}-${data.date_from}-to-${data.date_to}.xlsx`);
        } catch (e) {
            console.error(e);
            alert('Excel generation failed.');
        } finally {
            onEnd();
        }
    }
};
