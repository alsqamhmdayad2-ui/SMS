<!DOCTYPE html>
<html lang="{{ $template->language ?? 'en' }}" dir="{{ ($template->language ?? 'en') == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Report')</title>
    <style>
        body {
            font-family: '{{ $template->font_family ?? 'sans-serif' }}', sans-serif;
            font-size: 14px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header img {
            max-height: 80px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header h2 {
            margin: 5px 0 0 0;
            font-size: 18px;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        @if(($template->language ?? 'en') == 'ar')
        th, td {
            text-align: right;
        }
        @endif
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #777;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
        .signature-box {
            margin-top: 50px;
            width: 100%;
        }
        .signature-box table {
            border: none;
        }
        .signature-box td {
            border: none;
            text-align: center;
        }
        .qr-code {
            text-align: right;
        }
        @if(($template->language ?? 'en') == 'ar')
        .qr-code {
            text-align: left;
        }
        @endif
    </style>
</head>
<body>
    @if(isset($template) && $template->header)
        {!! $template->header !!}
    @else
        <div class="header">
            @if(isset($template) && $template->show_logo && isset($school->logo))
                <img src="{{ public_path($school->logo) }}" alt="School Logo">
            @endif
            <h1>{{ $school->school_name ?? 'School Name' }}</h1>
            @if(isset($school->school_name_en))
                <h2>{{ $school->school_name_en }}</h2>
            @endif
        </div>
    @endif

    <div class="content">
        @yield('content')
    </div>

    @if(isset($template) && $template->show_signature)
    <div class="signature-box">
        <table>
            <tr>
                <td>
                    <p>Teacher Signature</p>
                    <br><br>
                    <p>________________</p>
                </td>
                <td>
                    <p>Principal Signature</p>
                    @if(isset($school->principal_signature))
                        <img src="{{ public_path($school->principal_signature) }}" alt="Signature" height="50">
                    @else
                        <br><br>
                    @endif
                    <p>________________</p>
                </td>
            </tr>
        </table>
    </div>
    @endif

    <div class="footer">
        @if(isset($template) && $template->footer)
            {!! $template->footer !!}
        @else
            <p>{{ $school->report_footer ?? 'Official Document' }}</p>
            <p>Generated on {{ now()->format('Y-m-d H:i') }}</p>
        @endif
    </div>
</body>
</html>
