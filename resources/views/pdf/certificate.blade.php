<!DOCTYPE html>
<html>
<head>
    <title>Certificate of Compliance</title>
    <style>
        body { font-family: sans-serif; text-align: center; border: 10px solid #78350f; padding: 20px; }
        .container { border: 5px solid #78350f; padding: 20px; height: 90%; }
        h1 { font-size: 50px; color: #78350f; margin-bottom: 10px; }
        .subtitle { font-size: 20px; margin-bottom: 40px; }
        .recipient { font-size: 30px; font-weight: bold; border-bottom: 2px solid #333; display: inline-block; padding: 0 20px; margin: 20px 0; }
        .details { font-size: 18px; margin-top: 20px; }
        .footer { margin-top: 60px; display: flex; justify-content: space-between; padding: 0 50px; }
        .sign { border-top: 1px solid #333; width: 200px; padding-top: 10px; margin: 0 auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>CERTIFICATE OF COMPLIANCE</h1>
        <div class="subtitle">This certifies that</div>

        <div class="recipient">{{ $training->company_name }}</div>
        
        <p>Representative: {{ $training->representative_name }}</p>

        <p class="details">
            Has successfully completed the <strong>{{ $training->topic }}</strong><br>
            Conducted on {{ \Carbon\Carbon::parse($training->date_conducted)->format('F d, Y') }}
        </p>

        <div class="footer">
            <div style="float:left; width: 40%;">
                <div class="sign">Chief, Training Unit</div>
            </div>
            <div style="float:right; width: 40%;">
                <div class="sign">City Fire Marshal</div>
            </div>
        </div>
    </div>
</body>
</html>