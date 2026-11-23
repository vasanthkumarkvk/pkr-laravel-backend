<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .header { background-color: #b8860b; color: white; padding: 15px; text-align: center; border-radius: 5px 5px 0 0; }
        .header h2 { margin: 0; }
        .content { padding: 20px; }
        .field { margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
        .label { font-weight: bold; color: #b8860b; display: block; margin-bottom: 3px; }
        .footer { text-align: center; padding: 15px; font-size: 12px; color: #999; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>🤝 New Channel Partner Registration</h2>
        </div>
        <div class="content">
            <div class="field">
                <span class="label">Full Name:</span>
                {{ $partner->full_name }}
            </div>
            <div class="field">
                <span class="label">Phone:</span>
                {{ $partner->phone }}
            </div>
            <div class="field">
                <span class="label">Email:</span>
                {{ $partner->email }}
            </div>
            <div class="field">
                <span class="label">Profession:</span>
                {{ $partner->profession ?? 'Not specified' }}
            </div>
            <div class="field">
                <span class="label">City:</span>
                {{ $partner->city ?? 'Not specified' }}
            </div>
            <div class="field">
                <span class="label">About:</span>
                {{ $partner->about ?? 'N/A' }}
            </div>
        </div>
        <div class="footer">
            This email was sent from PKR Estates Channel Partner form.
        </div>
    </div>
</body>
</html>