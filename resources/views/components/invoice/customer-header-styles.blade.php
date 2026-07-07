<style>
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

    @media (max-width: 900px) {
        .invoice-document__header {
            flex-direction: column;
        }

        .invoice-document__title-block {
            text-align: left;
        }
    }

    @media print {
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
    }
</style>
