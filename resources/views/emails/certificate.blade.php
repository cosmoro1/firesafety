<!DOCTYPE html>
<html>
<head>
    <title>Training Certificate</title>
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .container { padding: 20px; border: 1px solid #ddd; }
        .header { color: #d32f2f; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="header">Certificate of Compliance</h1>
        <p>Dear {{ $training->representative_name }},</p>
        <p>This confirms that <strong>{{ $training->company_name }}</strong> has completed the training: {{ $training->topic }}.</p>
        <p>Status: <strong style="color:green;">ISSUED</strong></p>
    </div>
</body>
</html>