<!DOCTYPE html>
<html>
<head>
    <title>New Property Related Request Notification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #F4F4F4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 650px;
            margin: auto;
            padding: 30px;
            background: #FFFFFF;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border: 1px solid #ddd;
        }
        h1 {
            color: #007BFF;
            border-bottom: 2px solid #007BFF;
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-size: 24px;
        }
        .info {
            margin-bottom: 20px;
            padding: 15px;
            background: #F9F9F9;
            border-left: 5px solid #007BFF;
            border-radius: 5px;
        }
        .info strong {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-size: 16px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #777;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            font-size: 16px;
            color: #FFFFFF;
            background-color: #007BFF;
            border-radius: 5px;
            text-decoration: none;
            text-align: center;
            margin-top: 20px;
        }
        .button:hover {
            background-color: #0056B3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>
            @if($tourType === 'In-person' || $tourType === 'Virtual')
                New Property Tour Request
            @elseif($tourType === 'Wants more information')
                New Property Information Request
            @elseif($tourType === 'Broker wants to know what ExtraSlice offering to brokers')
                New Broker Inquiry Request
            @else
                New General Inquiry Request
            @endif
        </h1>
        <p class="info">
            <strong>Name:</strong> {{ $name }}
        </p>
        <p class="info">
            <strong>Email:</strong> {{ $email }}
        </p>
        <p class="info">
            <strong>Phone:</strong> {{ $phone }}
        </p>
        <p class="info">
            <strong>Tour Type Or Mail Purpose:</strong> {{ $tourType }}
        </p>
        @if(!empty($scheduled_date))
            <p class="info">
                <strong>Tour Scheduled For:</strong> {{ $scheduled_date }}
            </p>
        @endif
        <p class="info">
            <strong>Property ID:</strong> {{ $propertyId }}
        </p>
        <p class="info">
            <strong>Property Title:</strong> {{ $title }}
        </p>
        <p class="info">
            <strong>Property Location:</strong> {{ $location }}
        </p>
        <p class="info">
            <strong>Property Country:</strong> {{ $country }}
        </p>
        <p class="info">
            <strong>Property Zipcode:</strong> {{ $pincode }}
        </p>
    </div>
</body>
</html>
