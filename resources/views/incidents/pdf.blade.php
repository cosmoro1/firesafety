<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Incident Report</title>
    <style>
        /* PAGE SETTINGS */
        body { font-family: sans-serif; font-size: 12px; color: #333; line-height: 1.4; margin: 0; padding: 0; }
        @page { margin: 100px 50px; } /* Space for Header/Footer */

        /* HEADER */
        header { position: fixed; top: -80px; left: 0px; right: 0px; height: 100px; text-align: center; border-bottom: 2px solid #b91c1c; }
        .header-logo { height: 60px; width: auto; position: absolute; top: 0; left: 20px; }
        .header-text { margin-top: 10px; }
        .header-text h1 { margin: 0; font-size: 16px; text-transform: uppercase; color: #b91c1c; }
        .header-text p { margin: 2px 0; font-size: 10px; }

        /* FOOTER */
        footer { position: fixed; bottom: -60px; left: 0px; right: 0px; height: 50px; text-align: center; font-size: 10px; color: #666; border-top: 1px solid #ddd; padding-top: 10px; }
        .page-number:after { content: counter(page); }

        /* CONTENT */
        .watermark { position: fixed; top: 30%; left: 10%; width: 80%; opacity: 0.05; z-index: -1000; transform: rotate(-45deg); text-align: center; font-size: 80px; font-weight: bold; color: #000; }
        
        .section-title { font-size: 14px; font-weight: bold; background-color: #f3f4f6; padding: 8px; margin-top: 20px; margin-bottom: 10px; border-left: 4px solid #b91c1c; text-transform: uppercase; }
        
        /* TABLES */
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #e5e7eb; padding: 8px; text-align: left; vertical-align: top; }
        th { background-color: #f9fafb; font-weight: bold; width: 30%; color: #374151; }
        td { color: #111827; }

        /* IMAGES */
        .image-gallery { width: 100%; margin-top: 10px; }
        .evidence-img { width: 48%; height: 200px; object-fit: cover; margin-bottom: 10px; border: 1px solid #ccc; display: inline-block; margin-right: 2%; }
        
        /* SIGNATURES */
        .signatures { margin-top: 50px; page-break-inside: avoid; }
        .sig-box { width: 40%; float: right; text-align: center; }
        .sig-line { border-top: 1px solid #333; margin-top: 40px; margin-bottom: 5px; }
        .sig-name { font-weight: bold; text-transform: uppercase; }
        .sig-role { font-size: 10px; color: #666; }
    </style>
</head>
<body>

    <header>
        <div class="header-text">
            <p>Republic of the Philippines</p>
            <h1>Bureau of Fire Protection</h1>
            <p>Region 4A - Calamba City Fire Station</p>
            <p>FireIntel Management System</p>
        </div>
    </header>

    <footer>
        System Generated Report • {{ date('Y-m-d H:i:s') }} • Page <span class="page-number"></span>
    </footer>

    <div class="watermark">
        {{ $incident->status ?? 'RECORD' }}
    </div>

    <div style="margin-top: 20px;">
        
        <div style="text-align: right; margin-bottom: 20px;">
            <strong>CASE NO:</strong> INC-{{ str_pad($incident->id ?? $incident->incident_id, 6, '0', STR_PAD_LEFT) }}<br>
            <strong>DATE:</strong> {{ date('F d, Y') }}
        </div>

        <div class="section-title">I. Incident Overview</div>
        <table>
            <tr>
                <th>Investigation Stage</th>
                <td>{{ $incident->stage ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Date & Time of Incident</th>
                <td>{{ $incident->incident_date ?? $incident->created_at }}</td>
            </tr>
            <tr>
                <th>Location</th>
                <td>{{ $incident->location }}</td>
            </tr>
            <tr>
                <th>Incident Type</th>
                <td>{{ $incident->type }}</td>
            </tr>
            <tr>
                <th>Current Status</th>
                <td>{{ $incident->status }}</td>
            </tr>
        </table>

        <div class="section-title">II. Narrative Description</div>
        <div style="border: 1px solid #e5e7eb; padding: 15px; text-align: justify; min-height: 100px;">
            {{ $incident->description }}
        </div>

        <div class="section-title">III. Photographic Evidence</div>
        @if(!empty($imagePaths) && count($imagePaths) > 0)
            <div class="image-gallery">
                @foreach($imagePaths as $path)
                    {{-- DomPDF needs local file paths, not URLs --}}
                    <img src="{{ public_path('storage/' . $path) }}" class="evidence-img">
                @endforeach
            </div>
        @else
            <p style="color: #666; font-style: italic; padding: 10px;">No photographic evidence attached to this specific report version.</p>
        @endif

        <div class="signatures">
            <div class="sig-box">
                <p>Prepared & Verified By:</p>
                <div class="sig-line"></div>
                <div class="sig-name">{{ $incident->reported_by ?? 'System Administrator' }}</div>
                <div class="sig-role">Official Encoder / Officer</div>
            </div>
        </div>

    </div>
</body>
</html>