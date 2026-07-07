<style>
    .invoice-document {
        color: #1f2937;
        font-size: 13px;
        line-height: 1.5;
    }

    .invoice-document__section {
        border: 1px solid #d1d5db;
        border-radius: 10px;
        background: #fff;
        margin-bottom: 1.25rem;
        padding: 1.25rem 1.5rem;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
    }

    .invoice-document__header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1.5rem;
        padding-bottom: 1rem;
        margin-bottom: 1.25rem;
        border-bottom: 2px solid #e74a3f;
    }

    .invoice-document__logo {
        height: 36px;
        width: auto;
        flex-shrink: 0;
    }

    .invoice-document__title-block {
        text-align: right;
        flex: 1;
    }

    .invoice-document__title {
        margin: 0;
        font-size: 1.35rem;
        font-weight: 600;
        color: #182b63;
        letter-spacing: -0.01em;
    }

    .invoice-document__subtitle {
        margin: 0.35rem 0 0;
        font-size: 0.8125rem;
        color: #6b7280;
    }

    .invoice-document__section-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .invoice-document__section-heading h2 {
        margin: 0;
        font-size: 1rem;
        font-weight: 600;
        color: #374151;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .invoice-document__print-btn {
        border: 1px solid #374151;
        border-radius: 6px;
        background: transparent;
        padding: 0.25rem 0.75rem;
        font-size: 0.75rem;
        line-height: 1.4;
        cursor: pointer;
        color: inherit;
    }

    .invoice-document__print-btn:hover {
        background: #f3f4f6;
    }

    .invoice-document__grid {
        display: grid;
        gap: 1.25rem;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        margin-bottom: 1rem;
    }

    .invoice-document__label {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #e74a3f;
        margin: 0 0 0.5rem;
    }

    .invoice-document__address {
        margin: 0;
        color: #374151;
    }

    .invoice-document__meta {
        margin: 0.75rem 0 0;
        font-size: 0.8125rem;
        color: #4b5563;
    }

    .invoice-document__table-wrap {
        overflow-x: auto;
        margin-top: 0.5rem;
        scrollbar-width: none;
    }

    .invoice-document__table-wrap::-webkit-scrollbar {
        display: none;
    }

    .invoice-document__table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.8125rem;
    }

    .invoice-document__table thead th {
        background: #f8fafc;
        color: #182b63;
        text-align: left;
        font-weight: 600;
        border: 1px solid #e5e7eb;
        padding: 0.5rem 0.625rem;
    }

    .invoice-document__table tbody td {
        border: 1px solid #e5e7eb;
        padding: 0.5625rem 0.625rem;
        vertical-align: top;
        color: #374151;
    }

    .invoice-document__table tbody tr:nth-child(even) td {
        background: #fafafa;
    }

    .invoice-document__note {
        margin: 0.75rem 0 0;
        padding: 0.625rem 0.75rem;
        border-left: 3px solid #e74a3f;
        background: #fef7f6;
        font-size: 0.75rem;
        color: #4b5563;
    }

    .invoice-document__footer {
        margin-top: 1.25rem;
        padding-top: 0.75rem;
        border-top: 1px solid #e5e7eb;
        font-size: 0.6875rem;
        color: #9ca3af;
        text-align: center;
    }

    @media (max-width: 900px) {
        .invoice-document__grid {
            grid-template-columns: 1fr;
        }

        .invoice-document__header {
            flex-direction: column;
        }

        .invoice-document__title-block {
            text-align: left;
        }
    }

    @media print {
        @page {
            margin: 1.2cm 1.5cm;
            size: A4;
        }

        .fi-topbar,
        .fi-sidebar,
        .fi-sidebar-close-overlay,
        .fi-layout-sidebar,
        .fi-main-ctn > nav,
        .no-print,
        .invoice-document__print-btn {
            display: none !important;
        }

        body * {
            visibility: hidden;
        }

        .invoice-document,
        .invoice-document * {
            visibility: visible;
        }

        .invoice-document {
            position: absolute;
            inset: 0;
            width: 100%;
            background: #fff !important;
            color: #111827 !important;
            font-size: 11pt;
            line-height: 1.45;
            padding: 0 !important;
            margin: 0 !important;
        }

        .invoice-document__section {
            border: none;
            border-radius: 0;
            box-shadow: none;
            margin: 0 0 1.5rem;
            padding: 0 0 1rem;
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .invoice-document__section:not(:last-child) {
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 1.25rem;
        }

        .invoice-document__header {
            border-bottom-color: #e74a3f;
            padding-bottom: 0.75rem;
            margin-bottom: 1rem;
        }

        .invoice-document__logo {
            height: 32px;
        }

        .invoice-document__title {
            font-size: 18pt;
            color: #182b63 !important;
        }

        .invoice-document__subtitle {
            color: #4b5563 !important;
        }

        .invoice-document__section-heading {
            display: none;
        }

        .invoice-document__label {
            color: #182b63 !important;
            font-size: 9pt;
        }

        .invoice-document__table {
            font-size: 10pt;
        }

        .invoice-document__table thead th {
            background: #eef1f6 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .invoice-document__table tbody tr:nth-child(even) td {
            background: #f9fafb !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .invoice-document__note {
            background: #fef7f6 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .invoice-document__footer {
            color: #6b7280 !important;
        }

        body.invoice-print-single-section .invoice-document__section:not(.invoice-document__section--print-target) {
            display: none !important;
        }

        body.invoice-print-single-section .invoice-document__section--print-target {
            display: block !important;
            margin: 0 !important;
            padding: 0 !important;
            border: none !important;
        }
    }
</style>

<script>
    if (typeof window.printInvoiceSection !== 'function') {
        window.printInvoiceSection = function (sectionId) {
            const section = document.getElementById(sectionId);

            if (!section) {
                window.print();
                return;
            }

            section.classList.add('invoice-document__section--print-target');
            document.body.classList.add('invoice-print-single-section');

            const cleanup = () => {
                section.classList.remove('invoice-document__section--print-target');
                document.body.classList.remove('invoice-print-single-section');
                window.removeEventListener('afterprint', cleanup);
            };

            window.addEventListener('afterprint', cleanup);
            window.print();
        };
    }
</script>
