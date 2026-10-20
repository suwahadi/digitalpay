<style>
    .fi-simple-layout {
        background: #eef1f7;
    }

    .fi-simple-layout .fi-simple-main {
        width: calc(100% - 32px);
        max-width: 360px;
        padding: 28px 24px;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 0 0 1px #e4e8f0, 0 2px 4px rgba(15, 23, 42, 0.04), 0 24px 48px -16px rgba(15, 23, 42, 0.16);
    }

    .fi-simple-page > section {
        row-gap: 14px;
    }

    .fi-simple-page .fi-simple-header {
        align-items: flex-start;
    }

    .fi-simple-page .fi-simple-header-heading {
        font-size: 22px;
        line-height: 1.3;
        letter-spacing: -0.01em;
        text-align: left;
        color: #111827;
    }

    .fi-simple-page .fi-form,
    .fi-simple-page .fi-fo-component-ctn {
        row-gap: 16px;
    }

    .fi-simple-page .fi-fo-field-wrp-label span {
        font-size: 14px;
        font-weight: 600;
        color: #374151;
    }

    .fi-simple-page .fi-input-wrp {
        border-radius: 10px;
        background: #f8faff;
        box-shadow: inset 0 0 0 1px #d6dce7;
    }

    .fi-simple-page .fi-input-wrp:focus-within {
        box-shadow: inset 0 0 0 1px #0b74d1, 0 0 0 3px rgba(11, 116, 209, 0.18);
    }

    .fi-simple-page .fi-input-wrp.fi-invalid,
    .fi-simple-page .fi-input-wrp.fi-invalid:focus-within {
        box-shadow: inset 0 0 0 1px #dc2626, 0 0 0 3px rgba(220, 38, 38, 0.12);
    }

    .fi-simple-page .fi-input {
        padding-top: 10px;
        padding-bottom: 10px;
        font-size: 15px;
        color: #111827;
    }

    .fi-simple-page .fi-input::placeholder {
        color: #6b7280;
    }

    .fi-simple-page .fi-input-wrp-suffix {
        border-inline-start-width: 0;
        padding-inline-start: 0;
    }

    .fi-simple-page .fi-form-actions .fi-btn {
        min-height: 46px;
        border-radius: 10px;
        background: #0b74d1;
        font-size: 15px;
        font-weight: 700;
        color: #ffffff;
    }

    .fi-simple-page .fi-form-actions .fi-btn:hover {
        background: #0966b8;
    }

    .dark .fi-simple-layout {
        background: #0b1120;
    }

    .dark .fi-simple-layout .fi-simple-main {
        background: #111827;
        box-shadow: 0 0 0 1px #1f2937, 0 24px 48px -16px rgba(0, 0, 0, 0.5);
    }

    .dark .fi-simple-page .fi-simple-header-heading {
        color: #f9fafb;
    }

    .dark .fi-simple-page .fi-fo-field-wrp-label span {
        color: #d1d5db;
    }

    .dark .fi-simple-page .fi-input-wrp {
        background: #0f172a;
        box-shadow: inset 0 0 0 1px #334155;
    }

    .dark .fi-simple-page .fi-input-wrp:focus-within {
        box-shadow: inset 0 0 0 1px #3b8ee6, 0 0 0 3px rgba(59, 142, 230, 0.25);
    }

    .dark .fi-simple-page .fi-input-wrp.fi-invalid,
    .dark .fi-simple-page .fi-input-wrp.fi-invalid:focus-within {
        box-shadow: inset 0 0 0 1px #ef4444, 0 0 0 3px rgba(239, 68, 68, 0.2);
    }

    .dark .fi-simple-page .fi-input {
        color: #f3f4f6;
    }

    .dark .fi-simple-page .fi-input::placeholder {
        color: #9ca3af;
    }
</style>
