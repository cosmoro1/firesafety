<!DOCTYPE html>
<html>
<head>
    <title>Training Certificate</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; margin: 0; padding: 20px;">
    
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #ffffff;">
        
        <h1 style="color: #d32f2f; margin-top: 0;">Certificate of Compliance</h1>
        
        <p style="font-size: 16px; line-height: 1.5;">Dear {{ $training->representative_name }},</p>
        
        <p style="font-size: 16px; line-height: 1.5;">
            This email confirms that <strong>{{ $training->company_name }}</strong> has successfully completed the required training:
        </p>
        
        <div style="background-color: #f9f9f9; padding: 15px; border-left: 4px solid #d32f2f; margin: 20px 0;">
            <strong>Topic:</strong> {{ $training->topic }}<br>
            <strong>Date:</strong> {{ \Carbon\Carbon::parse($training->date_conducted)->format('F d, Y') }}
        </div>

        <p style="font-size: 16px;">
            Status: <strong style="color: green;">ISSUED</strong>
        </p>

        <hr style="border: none; border-top: 1px solid #eee; margin: 30px 0;">

        <p style="font-size: 12px; color: #888;">
            Please find the official certificates attached to this email.<br>
            This is an automated message from the BFP Safety System.
        </p>
    </div>

</body>
</html>