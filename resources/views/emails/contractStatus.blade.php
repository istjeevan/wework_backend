Hello,
<br> <br>
Below is your contract offer status.
<br>
Contract Id : {{ $contract->id }}
<br>
Property name : {{ $contract->contract_details->property_details->title }}
<br>
Status : {{ ucfirst($contract->status) }}
<br>
User Id : {{ $contract->user_id }}
<br>
Offered Price : {{ $contract->offered_price }}