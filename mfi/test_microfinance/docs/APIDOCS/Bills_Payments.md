1
Secret
NBC BILLS PAYMENTS ENGINE API DOCUMENT
Version v1.0.4
Owner In-House Development Team
Description
This is a document describing how channel(s) could integrate with NBC Bills 
Payments Engine, all APIs in this document are RESTful, and all requests and 
responses to/from BILLS PAYMENTS Engine are in Json format.
Purpose This document act as integration guide to NBC’s Bills Payments Engine. It can 
be consumed by Developers and Software Analysts
Consultations 
All questions or comments regarding this document should be forwarded to
InhouseDevelopments@nbc.co.tz copying Wilbard.Shirima@nbc.co.tz;
Dustan.Mbaga@nbc.co.tz; Alex.Nyerembe@nbc.co.tz;
Version History:
Version Date Description of changes/updates
v1.0.0 March 20, 2023 Initial Draft
v1.0.0 March 22, 2023 Review
v1.0.0 May 08, 2023 Updated Billers Retrieval
v1.0.0 June 11, 2023 Updated Inquiry Raw Response
v1.0.0 March 14, 2024 Added Extra fields to Inquiry Request Payloads
v1.0.1 April 02, 2024 Added Extra fields Content for NIDC and Yanga Services
v1.0.2 June 18, 2024 Updated Extra fields Content for Membership (Yanga Services)
v1.0.3 Nov 5, 2024 Added Extra fields Content for DSE Integration
v1.0.4 Feb 14, 2025 Added SP Code instructions to Key Points
KEY POINTS TO NOTE.
- NBC will share Base Endpoints for UAT and PROD
- All requests to “NBC Bills Payments Engine” will be through HTTPS.
(HTTP might be used on UAT for quick testing however HTTPS is preferred and on PROD only 
HTTPS will be used)
- Authentication method to be used will be Basic Authentication
Basic Auth Credentials will be configured and securely shared to the connecting channel.
PROD credentials will be shared after successfully completing SIT and UAT Sign-off.
- IP whitelisting might be put in place as well.
- SP Code value on Payment Request should come from SP Code value returned on Inquiry 
Response 
GENERIC BILLS PAYMENTS IMPLEMENTATION ON CHANNEL(S):
The Bills Payments Engine is designed to provide a flexible and customizable gateway that can be 
easily consumed by various channels. To achieve this, the engine exposes a set of generic APIs that 
are intended to be consumed by each channel or service. This engine will be responsible for handling 
accounting as well as notifying billers and customers.
As a Channel developer, you need to:
1. Consume Inquiry API
2. Consume Payment API
3. Develop and expose Payment Notification Callback
(When async payment approach is applicable)
2
Secret
4. Consume Status Check API
5. Consume Biller Retrieval:
From this you will get list of all available billers (integrated through this generic/central 
engine). Cache the list for 8 hours.
HIGH-LEVEL ARCHITECTURE:
Below is the high-level architecture of the engine, to show you involved systems and how will 
requests flow.
Channel X
Bills 
Payments
Engine
Biller 1
Biller 2
Biller N
Core Banking 
System
(FCR / FCC)
Midleware
Notification 
Engine
3
Secret
Upon successfully processing of the bill payment, biller will be notified by the engine. As well, 
customer will be notified through SMS. Below is the sample SMS that will be sent to the customer 
from Bills Payments Engine:
Sample SMS 
Notification to 
Customer
Mpendwa Julius Nyerere, Malipo yamekamilika kwenda Bugando Hospital.
Ankara: 400005265
Kiasi: 5000 TZS
Tarehe: 16-Mar-2023 07:59:17 
Risiti: SR2300000000103 
Kumbukumbu: CB2303161004081 
NBC Daima Karibu Nawe.
Channel may provide a receipt to customer, the template of all Bills Payments receipts that needs to 
be produced by any NBC’s will be shared to the connecting channels.
4
Secret
HEADER PARAMETERS
Below are header parameters to be set by the client/downstream system
Header 
Parameter
Required Sample Value
Content-Type Yes application/json
Accept Yes application/json
Authorization Yes Basic TkJDX1VTRVJOQU1FOk5CQ19QQVNTV09SRA==
Digital-Signature Yes VEhJUyBET0NVTUVOVCBJUyBQUkVQQVJFRCBCWSBEVVNU
QU4gTUJBR0EgRlJPTSBJTk5PVkFUSU9OIEFORCBERVZFTE9Q
TUVOVCBURUFNIEZST00gTkJDIEJBTksgVEFOWkFOSUEgKFB
BUlQgT0YgQUJTQSBHUk9VUCk=
Timestamp No 2021-07-05T17:08:00.00
1. INQUIRY API
The Inquiry API enables channel/system to request for Bill Details from service providers (also known 
as Biller). Engine will validate the request, upon success, will route the request to the biller, and 
return bills details.
HTTP Method POST
Endpoint /bills-payments-engine/api/v1/inquiry
Request Payload {
 "channelId": "CBP1010101",
 "spCode": "BPE0001000BC",
 "requestType": "inquiry",
 "timestamp": "2023-03-07T12:29:50.968",
 "userId": "USER101",
 "branchCode": "015",
 "channelRef": "520DAN18311100298",
 "billRef": "PE0123456789",
 "extraFields": {}
}
Success Response 
Payload
{
 "statusCode": "600",
 "message": "Success",
 "channelId": "CBP1010101",
 "spCode": "PE0001000BC",
 "requestType": "inquiry",
 "channelRef": "520DAN18311100298",
 "timestamp": "2023-03-07T12:29:50.968",
 "billDetails": {
 "billRef": "PE0123456789",
 "serviceName": "AABBCC",
 "description": "AABBCC Details",
 "billCreatedAt": "2023-03-10T10:45:20",
 "totalAmount": "30000",
 "balance": "25000",
 "phoneNumber": "255715000000",
 "email": "Nyerere.Julias@example.com",
 "billedName": "Nyerere Julias",
 "currency": "TZS",
5
Secret
 "paymentMode": "exact ",
 "expiryDate": "20230310T104520",
 "creditAccount": "0122****1486",
 "creditCurrency": "TZS",
 "extraFields": {}
 },
 "inquiryRawResponse": "{\"statusCode\": \"600\",\"message\": 
\"Success\",\"channelId\": \"CBP1010101\",\"spCode\": 
\"PE0001000BC\",\"requestType\": \"inquiry\",\"channelRef\": 
\"520DAN18311100298\",\"timestamp\": \"2023-03-
07T12:29:50.968\",\"billDetails\": {\"billRef\": 
\"PE0123456789\",\"serviceName\": \"AABBCC\",\"description\": \"AABBCC 
Details\",\"billCreatedAt\": \"2023-03-10T10:45:20\",\"totalAmount\": 
\"30000\",\"balance\": \"25000\",\"phoneNumber\": 
\"255715000000\",\"email\": 
\"Nyerere.Julias@example.com\",\"billedName\": \"Nyerere 
Julias\",\"currency\": \"TZS\",\"paymentMode\": \"exact \",\"expiryDate\": 
\"20230310T104520\",\"creditAccount\": 
\"0122****1486\",\"creditCurrency\": \"TZS\",\"extraFields\": {}}}"
}
Failure Response 
Payload
{
 "statusCode": 602,
 "message": "Service Provider SP1001BP001 is is currently inactive",
 "spCode": "SP1001BP001",
 "channelId": "CBP1010101",
 "requestType": "inquiry",
 "channelRef": "520DAN18311100298",
 "timestamp": "2023-03-17T17:08:17.98601",
 "data": {}
}
2. PAYMENT API
The payment API enables channel(s) to confirm bills payment details and authorize payment of the 
bill. Hence, approves that Bills Payment Engine should proceed processing the request and sending 
request to CBS through middleware to debit a given amount from Customer’s account and be 
credited to the billers account.
HTTP Method POST
Endpoint /bills-payments-engine/api/v1/payment
Request Payload {
 "channelId": "CBP1010101",
 "spCode": "BPE0001000BC",
 "requestType": "payment",
 "approach": "async|sync",
 "callbackUrl": "https://nbc.co.tz:443/channel-nbc/api/v1/callback-url",
 "timestamp": "2023-03-07T12:29:50.968",
 "userId": "USER101",
6
Secret
 "branchCode": "015",
 "billRef": "PE0123456789",
 "channelRef": "520DAN183111002",
 "amount": "25000",
 "creditAccount": "0122****1486",
 "creditCurrency": "TZS",
 "debitAccount": "280120400",
 "debitCurrency": "TZS",
 "paymentType": "ACCOUNT ",
 "channelCode": "APP ",
 "payerName": "Nyerere Julias",
 "payerPhone": "255715000000",
 "payerEmail": "Nyerere.Julias@example.com",
 "narration": "Test Bills Payments",
 "extraFields": {},
 "inquiryRawResponse": "{\"statusCode\": \"600\",\"message\": 
\"Success\",\"channelId\": \"CBP1010101\",\"spCode\": 
\"PE0001000BC\",\"requestType\": \"inquiry\",\"channelRef\": 
\"520DAN18311100298\",\"timestamp\": \"2023-03-
07T12:29:50.968\",\"billDetails\": {\"billRef\": 
\"PE0123456789\",\"serviceName\": \"AABBCC\",\"description\": \"AABBCC 
Details\",\"billCreatedAt\": \"2023-03-10T10:45:20\",\"totalAmount\": 
\"30000\",\"balance\": \"25000\",\"phoneNumber\": 
\"255715000000\",\"email\": 
\"Nyerere.Julias@example.com\",\"billedName\": \"Nyerere 
Julias\",\"currency\": \"TZS\",\"paymentMode\": \"exact \",\"expiryDate\": 
\"20230310T104520\",\"creditAccount\": 
\"0122****1486\",\"creditCurrency\": \"TZS\",\"extraFields\": {}}}"
}
This API support both Sync and Async. Between the two approaches, Async is the preferred one.
A. ASYNC PAYMENT APPROACH 
Bills Payments Engine will respond with success (acknowledging that the request has been 
successfully received and validated). Then engine will proceed with processing of the request, 
perform accounting, notify biller, and then update channel backend application by posting the 
response to the callback provided on the payload.
- Initial acknowledgement response
Success Response 
Payload
{
 "statusCode": "600",
 "message": "Received and validated, engine is now processing your request",
 "channelId": "CBP1010101",
 "spCode": "PE0001000BC",
 "requestType": "payment",
 "channelRef": "520DAN183111002",
 "gatewayRef": "PE12371273189238721",
 "timestamp": "2023-03-07T12:29:50.968",
 "paymentDetails": null
7
Secret
}
Failure Response 
Payload
{
 "statusCode": "601",
 "message": "Error, (207) Transaction reference number already paid",
 "spCode": "BPE0001000BC",
 "channelId": "CBP1010101",
 "requestType": "payment",
 "channelRef": "520DAN183111002",
 "timestamp": "2023-03-20T09:56:29.684191",
 "paymentDetails": null
}
- Callback request to channel backend system
Channel Backend is required to develop and expose an API/Service that will be consumed by Bills 
Payments Engine. 
HTTP Method POST
Endpoint Engine will be receiving channel callback on the payment request.
Request Payload {
 "statusCode": "600",
 "message": "Success",
 "channelId": "CBP1010101",
 "spCode": "PE0001000BC",
 "requestType": "payment",
 "channelRef": "520DAN183111002",
 "timestamp": "2023-03-07T12:29:50.968",
 "paymentDetails": {
 "billRef": "PE0123456789",
 "gatewayRef": "PE12371273189238721",
 "amount": "25000",
 "currency": "TZS",
 "transactionTime": "20230310T104520",
 "billerReceipt": "RCPT283432988",
 "remarks": "Successfully received",
 "extraFields": {}
 }
}
Success Response 
Payload
{
 "statusCode": "600",
 "message": "Success",
 "billRef": "PE0123456789",
 "channelRef": "520DAN183111002",
 "gatewayRef": "PE12371273189238721"
}
Failure Response 
Payload
{
 "statusCode": "601",
8
Secret
 "message": "Unknow channel reference number”
}
B. SYNC PAYMENT APPROACH 
With this, Channel will need to block and wait for the actual processing response from Bills Payments 
Engine. This implementation is highly discouraged due below facts: 
i. Performance and Scalability: With synchronous, each operation must complete before the 
next operation can start, which can cause delays and slow down the application. Synchronous 
may also limit the scalability of the application. As the number of users and requests increase, 
the synchronous approach may become overwhelmed, leading to delays and timeouts. This 
can ultimately impact the user experience and the overall performance of the application.
ii. User Experience: Banking applications are often accessed by users who expect a fast and 
responsive experience. With synchronous, users may experience delays and slow responses, 
leading to frustration and dissatisfaction. Asynchronous can provide a more responsive and 
seamless user experience, as it allows the application to continue processing other operations 
while waiting for long-running operations to complete.
iii. Integration with External Systems: Application interact with external systems, such as biller
gateways or third-party service providers. Synchronous approach may not be suitable for 
these types of interactions, as external systems may have varying response times and 
availability. Asynchronous can provide better integration with external systems, as it allows 
the application to continue processing other operations while waiting for responses from 
external systems.
Sync success / failure response payloads:
Success Response 
Payload
{
 "statusCode": "600",
 "message": "Success",
 "channelId": "CBP1010101",
 "spCode": "PE0001000BC",
 "requestType": "payment",
 "channelRef": "520DAN183111002",
 "timestamp": "2023-03-07T12:29:50.968",
 "paymentDetails": {
 "billRef": "PE0123456789",
 "gatewayRef": "PE12371273189238721",
 "amount": "25000",
 "currency": "TZS",
 "transactionTime": "20230310T104520",
 "billerReceipt": "RCPT283432988",
 "remarks": "Successfully received",
 "extraFields": {}
 }
}
Failure Response 
Payload
{
 "statusCode": "601",
9
Secret
 "message": "Error, (207) Transaction reference number already paid",
 "spCode": "BPE0001000BC",
 "channelId": "CBP1010101",
 "requestType": "payment",
 "channelRef": "520DAN183111002",
 "timestamp": "2023-03-20T09:56:29.684191",
 "paymentDetails": null
}
3. STATUS CHECK 
The Status Check API enables channel(s) to inquire status of the Bill Payment on the Engine (not 
biller). Hence, engine will respond with status indicating whether the accounting was done, and biller 
notified.
HTTP Method POST
Endpoint /bills-payments-engine/api/v1/status-check
Request Payload {
 "channelId": "CBP1010101",
 "spCode": "BPE0001000BC",
 "requestType": "statusCheck",
 "timestamp": "2023-03-07T12:29:50.968",
 "channelRef": "520DAN183111002",
 "billRef": "PE0123456789",
 "extraFields": {}
}
Success Response 
Payload
{
 "statusCode": "600",
 "message": "Success",
 "channelId": "CBP1010101",
 "spCode": "PE0001000BC",
 "requestType": "statusCheck",
 "channelRef": "520DAN183111002",
 "timestamp": "2023-03-07T12:29:50.968",
 "paymentDetails": {
 "billRef": "PE0123456789",
 "gatewayRef": "PE12371273189238721",
 "amount": "25000",
 "currency": "TZS",
 "transactionTime": "20230310T104520",
 "billerReceipt": "RCPT283432988",
 "remarks": "Successfully received",
 "accountingStatus": "success",
 "billerNotified": "processed|InProgress",
 "extraFields": {}
 }
}
Failure Response 
Payload
{
 "statusCode": "603",
10
Secret
 "message": "Error, unknown Channel ID",
 "spCode": "BPE0001000BC",
 "channelId": "CBP1010101",
 "requestType": "statusCheck",
 "channelRef": "520DAN183111002",
 "timestamp": "2023-03-20T09:56:29.684191",
 "paymentDetails": null
}
4. BILLERS RETRIEVAL API
This API give the channel ability to retrieve all available billers integrated through Bills Payments 
Engine. Channel is supposed to fetch the list and cache the details for 8 Hours on its backend 
application. Then from the list channel will proceed with implementation of the navigations basing on 
the business requirements and customer experiences recommendations. From the list, channel is 
supposed to show only active billers to the users/customers.
HTTP Method POST
Endpoint /bills-payments-engine/api/v1/billers-retrieval
Request Payload {
 "channelId": "CBP1010101",
 "requestType": "getServiceProviders",
 "timestamp": "2023-03-07T12:29:50.968",
 "channelRef": "520DAN183111002"
}
Success Response 
Payload
{
 "statusCode": "600",
 "message": "Success",
 "channelId": "CBP1010101",
 "requestType": "getServiceProviders",
 "channelRef": "520DAN183111002",
 "timestamp": "2023-03-07T12:29:50.968",
 "serviceProviders": [
 {
 "spCode": "PE0001001BC",
 "shortName": "Bugando",
 "fullName": "Bugando Hospital",
 "active": true,
 "category": "hospital",
 "spIcon": " VEhJUyBET0NVTUVOV…="",
 "categoryIcon": " VEhJUyBET0NVTUVOV…=""
 },
 {
 "spCode": "PE0001002BC",
 "shortName": "Muhimbili",
 "fullName": "Muhimbili Hospital",
 "active": false,
 "category": "hospital",
11
Secret
 "spIcon": " VEhJUyBET0NVTUVOV…="",
 "categoryIcon": " VEhJUyBET0NVTUVOV…=""
 },
 {
 "spCode": "PE0002001BC",
 "shortName": "AghaKhan Sec",
 "fullName": "Agakhan Secondary School",
 "active": true,
 "category": "school",
 "spIcon": " VEhJUyBET0NVTUVOV…="",
 "categoryIcon": " VEhJUyBET0NVTUVOV…=""
 },
 {
 "spCode": "PE0002002BC",
 "shortName": "Tambaza",
 "fullName": "Tambaza Primary",
 "active": false,
 "category": "school",
 "spIcon": " VEhJUyBET0NVTUVOV…="",
 "categoryIcon": " VEhJUyBET0NVTUVOV…=""
 },
 {
 "spCode": "PE0003001BC",
 "shortName": "ZIC",
 "fullName": "Zanzibar Insurance Company",
 "active": true,
 "category": "insurance",
 "spIcon": " VEhJUyBET0NVTUVOV…="",
 "categoryIcon": " VEhJUyBET0NVTUVOV…=""
 }
 ]
}
Failure Response 
Payload
{
 "statusCode": "603",
 "message": "Error, unknown Channel ID",
 "channelId": "CBP1010101",
 "requestType": "getServiceProviders",
 "channelRef": "520DAN183111002",
 "timestamp": "2023-03-20T09:56:29.684191"
}
 
 
 
 
 
 
 
12
Secret
5. NIDC BASED API
This API guides channel through additional attributes needed to be included on NIDC requests mostly 
under extra fields attribute for both requests and responses. However, failed and error responses 
remain the same to the general failed and error response.
5.1. Events Tickets
5.1.1. Events Tickets Inquiry
5.1.1.1. Inquiry Ticket Categories
Extra Fields 
("extraFields") 
Content for 
Request Payload
to get Ticket 
Categories.
{
 "inquiryType": "CATEGORIES"
}
Extra Fields 
("extraFields") 
Content for 
Success Response 
Payload
{
 "result": [
 {
 "serviceName": "FOOTBALL TICKETS",
 "serviceCode": "CN001"
 }
 ]
}
5.1.1.2. Inquiry Active Events
Extra Fields 
("extraFields") 
Content for 
Request Payload
to get Active 
Events.
{
 "inquiryType": "ACTIVE-EVENTS",
 "serviceCode": "CN001"
}
Extra Fields 
("extraFields") 
Content for 
Success Response 
Payload
{
 "result": [
 {
 "eventName": "SIMBA DAY",
 "eventCode": "001AB1"
 }
 ]
}
5.1.1.3. Inquiry Ticket Class Category
Extra Fields 
("extraFields") 
Content for 
Request Payload
to get Ticket Class 
Category.
{
 "inquiryType": "TICKET-CLASS",
 "eventCode": "001AB1"
}
13
Secret
Extra Fields 
("extraFields") 
Content for 
Success Response 
Payload
{
 "result": [
 {
 "categoryName": "YANGA SEASON-MZUNGUKO",
 "categoryCode": "YNC01",
 "price": 80000.0,
 "priceCode": "1234",
 "ownerCode": "YNC"
 }
 ]
}
5.1.2. Events Tickets Payment 
Extra Fields 
("extraFields") 
Content for 
Request Payload
to Purchase
Ticket.
{
 "eventCode": "001AB1",
 "categoryCode": "YNC01",
 "priceCode": "1234",
 "ownerCode": "YNC"
}
Extra Fields 
("extraFields") 
Content for 
Success Response 
Payload
{}
 
6. MEMBERSHIP CATEGORY BASED API
This API guides channel through additional attributes needed to be included on Membership Category 
Based requests mostly under extra fields attribute under both requests and success responses.
However, failed and error responses remain the same to the general failed and error response.
6.1. Yanga Inquiry
6.1.1. Inquiry Member Status
Extra Fields 
("extraFields") 
Content for 
Request Payload
to get member 
status.
{
 "inquiryType": "STATUS-CHECK",
 "filter": {
 "mobileNumber": "0766811679",
 "memberID": "TZDAR01039013"
 }
}
Extra Fields 
("extraFields") 
Content for 
Success Response 
Payload
{
 "memberID": "TZDAR01039013",
 "expiryDate": "2024-06-30",
 "expiryStatus": "ACTIVE"
}
14
Secret
6.1.2. Inquiry Services and Locations
Extra Fields 
("extraFields") 
Content for 
Request Payload
to get available
services and 
locations for 
Yanga
branches which 
shall be used
for pulling 
branches on a
specific locations
{
 "inquiryType": "GET-SERVICES",
 "filter": {}
}
Extra Fields 
("extraFields") 
Content for 
Success Response 
Payload
{
 "services": [
 {
 "serviceID": "YANGA",
 "name": "Register Member",
 "amount": "29000"
 }
 ],
 "branches": [
 {
 "id": 332,
 "name": "Usariver",
 "code": "TZARS02001",
 "codeCountry": "TZ",
 "zone": "Kanda ya Kaskazini",
 "region": "Arusha",
 "district": "Arumeru",
 "address": "Arusha-Arumeru",
 "autonumTrack": 135,
 "branchStatus": "Inactive"
 }
 ],
 "locations": [
 {
 "id": 1,
 "name": "Kanda ya Kaskazini",
 "desrc": "Kanda ya Kaskazini",
 "parentID": 0,
 "levelID": "level1",
 "levelName": "level1",
 "divAbrev": "",
 "metadata": null
 }
 ]
}
15
Secret
6.1.3. Inquiry Branches
Extra Fields 
("extraFields") 
Content for 
Request Payload
to get branches in 
the specified
location
{
 "inquiryType": "GET-BRANCHES",
 "filter": {
 "zone": "Kanda ya Kaskazini",
 "region": "Arusha",
 "district": "Arumeru"
 }
}
Extra Fields 
("extraFields") 
Content for 
Success Response 
Payload
{
 "barnches": [
 {
 "id": 332,
 "name": "Usariver",
 "code": "TZARS02001",
 "codeCountry": "TZ",
 "zone": "Kanda ya Kaskazini",
 "region": "Arusha",
 "district": "Arumeru",
 "address": "Arusha-Arumeru",
 "autonumTrack": 135,
 "branchStatus": "Inactive"
 }
 ]
}
6.2. Yanga Payment
6.2.1. Payment For Member Registration
Extra Fields 
("extraFields") 
Content for 
Request Payload
to register new 
member
{
 "serviceID": "YANGA",
 "fName": "Davis",
 "lName": "Bazicha",
 "branchName": "Furaha ya Yanga",
 "dob": "1986-11-05",
 "gender": "Male",
 "country": "TZ",
 "branchID": "TZDAR02001",
 "zone": "Kanda ya Pwani",
 "region": "Dar-es-Salaam",
 "district": "Kinondoni",
 "imagePATH": "placeholder.png"
}
Extra Fields 
("extraFields") 
Content for 
Success Response 
Payload
{
 "memberID": "TZDAR02001149",
 "expiryDate": "2024-06-30",
 "expiryStatus": "ACTIVE"
}
16
Secret
6.2.2. Payment For Member Renewal
Extra Fields 
("extraFields") 
Content for 
Request Payload
to renew existing
membership
subscription
{
 "serviceID": "YANGA",
 "memberID": "TZDAR01039013",
 "phone": "0766811679",
 "branchID": "TZDAR01039",
 "branchName": "Pugu Misliver"
}
Extra Fields 
("extraFields") 
Content for 
Success Response 
Payload
{
 "memberID": "TZDAR01039013",
 "expiryDate": "2024-06-30",
 "expiryStatus": "ACTIVE"
}
7. DSE BASED API
This API guides channel through additional attributes needed to be included on DSE requests mostly 
under extra fields attribute on the inquiry payload for both requests and responses. However, failed 
and error responses remain the same to the general failed and error response.
7.0.1. Investor Registration
Extra Fields 
("extraFields") 
Content for 
Request Payload
to post account 
details.
{
 "inquiryType": "INVESTOR-REGISTRATION",
 "birthDistrict": "XXX",
 "birthWard": "XXX",
 "brokerRef": "858d26afae15419695ebf4f69ea82725",
 "country": "Tanzania",
 "dob": "YYYY-MM-DD",
 "email": "XXX@XXX.XXX",
 "firstName": "XXX",
 "gender": "MALE/FEMALE",
 "lastName": "XXX",
 "middleName": "XXX",
 "nationality": "Tanzania",
 "nidaNumber": "XXXXXXXXXXXXXXXXXXXX",
 "phoneNumber": "0XXXXXXXXX",
 "photo": "PHN2ZyB4bWPC9zdmc+",
 "physicalAddress": "XXX",
 "placeOfBirth": "Kinondoni",
 "region": "Dar es Salaam",
 "residentDistrict": "Kinondoni",
 "residentHouseNo": "120",
 "residentPostCode": "14030",
 "residentRegion": "Dar es Salaam",
 "residentVillage": "Kinondoni"
}
Extra Fields 
("extraFields") 
Content for 
{
 "result": {
 "brokerName": "SOLOMON STOCKBROKERS LIMITED.",
 "mobileNumber": "0XXXXXXXXX",
17
Secret
Success Response 
Payload
 "nidaNumber": "XXXXXXXXXXXXXXXXXXXX"
 }
}
7.0.2. Investor Account Verification
Extra Fields 
("extraFields") 
Content for 
Request Payload.
{
 "inquiryType": "INVESTOR-ACC-VERIFICATION",
 "csdAccount": "XXXXXX",
 "nidaNumber": "XXXXXXXXXXXXXXXXXXXX"
}
Extra Fields 
("extraFields") 
Content for 
Success Response 
Payload
{
 "result": {
 "csdAccount": "XXXXXX",
 "mobileNumber": "0XXXXXXXXX",
 "nidaNumber": "XXXXXXXXXXXXXXXXXXXX"
 }
}
 
7.0.3. Investor Account Details
Extra Fields 
("extraFields") 
Content for 
Request Payload.
{
 "inquiryType": "INVESTOR-ACC-DETAILS",
 "nidaNumber": "XXXXXXXXXXXXXXXXXXXX"
}
Extra Fields 
("extraFields") 
Content for 
Success Response 
Payload
{
 "result": {
 "birthDistrict": "XXX",
 "birthWard": "XXX",
 "brokerRef": "fc598326bcac42f2b0c3b5592f933800",
 "country": "Tanzania",
 "dob": "YYYY-MM-DD",
 "email": "XXX@XXX.XXX",
 "firstName": "XXX",
 "gender": "MALE",
 "lastName": "XXX",
 "middleName": "XXX",
 "nationality": "Tanzania",
 "nidaNumber": "XXXXXXXXXXXXXXXXXXXX",
 "phoneNumber": "0XXXXXXXXX",
 "photo": null,
 "physicalAddress": null,
 "placeOfBirth": "Kinondoni",
 "region": "Dar es Salaam",
 "residentDistrict": "Kinondoni",
 "residentHouseNo": "120",
 "residentPostCode": "14030",
 "residentRegion": "Dar es Salaam",
 "residentVillage": "Kinondoni"
 }
}
 
18
Secret
7.0.4. Brokers List 
Extra Fields 
("extraFields") 
Content for 
Request Payload.
{
 "inquiryType": "GET-BROKERS"
}
Extra Fields 
("extraFields") 
Content for 
Success Response 
Payload
{
 "brokers": [
 {
 "email": "XXX@XXX.XXX",
 "mobileNumber": "255XXXXXXXXX",
 "name": "XXX STOCKBROKERS LIMITED.",
 "physicalAddress": "Po Box XXX, XXX XXX XXX",
 "reference": "858d26afae15419695ebf4f69ea82725"
 },
 {
 "email": "XXX@XXX.XXX",
 "mobileNumber": "0XXXXXXXXX",
 "name": "XXX FINANCHIAL",
 "physicalAddress": "PO BOX XXX",
 "reference": "ae35edd7904c4f04907671520faf6df7"
 }
 ]
}
 
7.0.5. Market Data 
Extra Fields 
("extraFields") 
Content for 
Request Payload.
{
 "inquiryType": "INVESTOR-GET-MARKET-DATA",
 "nidaNumber": "XXXXXXXXXXXXXXXXXXXX"
}
Extra Fields 
("extraFields") 
Content for 
Success Response 
Payload
{
 "result": [
 {
 "bestBidPrice": 0.0,
 "bestBidQuantity": 0.0,
 "bestOfferPrice": 0.0,
 "bestOfferQuantity": 0.0,
 "change": 0.0,
 "high": 0.0,
 "low": 0.0,
 "marketCap": 3.26479823E11,
 "marketPrice": 125.0,
 "maxPriceLimit": 131.25,
 "minPriceLimit": 118.75,
 "openingPrice": 125.0,
 "percentageChange": 0.0,
 "securityName": "XXX",
 "securityRef": "a2b5d71b939e4a11b82647640c5eb87e",
 "time": "2024-10-15 09:30:01.9417836",
 "priceMultiplesOf": 5.0,
 "volume": 0.0,
19
Secret
 "companyName": "XXX BANK PUBLIC LIMITED COMPANY"
 }
 ]
}
 
7.0.6. Buy Shares 
Extra Fields 
("extraFields") 
Content for 
Request Payload
{
 "inquiryType": "INVESTOR-BUY-SHARE",
 "nidaNumber": "19980202972200007447",
 "price": 124,
 "securityReference": "a2b5d71b939e4a11b82647640c5eb87e",
 "shares": 10
}
Extra Fields 
("extraFields") 
Content for 
Success Response 
Payload
{
 "securityId": "LPPJ",
 "securityName": "XXX",
 "orderReference": "5a6125b8e61e4b8299c3fd00c9717e69",
 "orderStatus": "Pending",
 "shares": 10,
 "price": 123.0,
 "orderDate": "2024-11-05 11:10:40",
 "considerationAmount": 1230.0,
 "commission": 30.0,
 "totalAmount": 1260.0
}
 
7.0.7. Buy Orders 
Extra Fields 
("extraFields") 
Content for 
Request Payload
{
 "inquiryType": "INVESTOR-BUY-ORDER",
 "nidaNumber": "19980202972200007447",
 "orderStatus": "Pending",
 "startDate": "2024-09-13",
 "endDate": "2024-10-13"
}
Extra Fields 
("extraFields") 
Content for 
Success Response 
Payload
{
 "result": [
 {
 "shares": 10,
 "price": 123.0,
 "securityId": "LPPJ",
 "orderStatus": "Pending",
 "commission": 30.0,
 "orderReference": "c378c65573134238b0d552f3fe3",
 "brokerReference": "fc598326bcac42f2b0c3b5592f93",
 "orderDate": "2024-09-13 17:32:33",
 "controlNumber": "995430001985",
 "investorFullName": null,
 "securityName": "XXX",
 "brokerName": "XXX XXX XXX XXX",
 "considerationAmount": 1230.0,
20
Secret
 "totalAmount": 1260.0,
 "rejectionReason": null,
 "isPaid": true
 }
 ]
}
 
7.0.8. Share Holdings 
Extra Fields 
("extraFields") 
Content for 
Request Payload
{
 "inquiryType": "INVESTOR-SHARE-HOLDINGS",
 "nidaNumber": "19980202972200007447"
}
Extra Fields 
("extraFields") 
Content for 
Success Response 
Payload
{
 "result": [
 {
 "brokerName": "XXX",
 "brokerReference": "XXX",
 "freeBalance": 0,
 "nidaNumber": "XXX",
 "pledgedBalance": 0,
 "securityId": "XXX",
 "securityName": "XXX",
 "totalBalance": 0
 }
 ]
}
 
7.0.9. Sell Shares 
Extra Fields 
("extraFields") 
Content for 
Request Payload
{
 "inquiryType": "INVESTOR-SELL-SHARE",
 "nidaNumber": "19980202972200007447",
 "price": 124,
 "securityReference": "a2b5d71b939e4a11b82647640c5eb87e",
 "shares": 10
}
Extra Fields 
("extraFields") 
Content for 
Success Response 
Payload
{
 "securityId": "LPPJ",
 "securityName": "XXX",
 "orderReference": "5a6125b8e61e4b8299c3fd00c9717e69",
 "orderStatus": "Pending",
 "shares": 10,
 "price": 123.0,
 "orderDate": "2024-11-05 11:10:40",
 "considerationAmount": 1230.0,
 "commission": 30.0,
 "totalAmount": 1260.0
}
21
Secret
7.1.0. Sell Orders 
Extra Fields 
("extraFields") 
Content for 
Request Payload
{
 "inquiryType": "INVESTOR-SELL-ORDER",
 "nidaNumber": "19980202972200007447",
 "orderStatus": "Pending",
 "startDate": "2024-09-13",
 "endDate": "2024-10-13"
}
Extra Fields 
("extraFields") 
Content for 
Success Response 
Payload
{
 "result": [
 {
 "shares": 10,
 "price": 123.0,
 "securityId": "LPPJ",
 "orderStatus": "Pending",
 "commission": 30.0,
 "orderReference": "c378c65573134238b0d552f3fe3",
 "brokerReference": "fc598326bcac42f2b0c3b5592f93",
 "orderDate": "2024-09-13 17:32:33",
 "controlNumber": "995430001985",
 "investorFullName": null,
 "securityName": "XXX",
 "brokerName": "XXX XXX XXX XXX",
 "considerationAmount": 1230.0,
 "totalAmount": 1260.0,
 "rejectionReason": null,
 "isPaid": true
 }
 ]
}
 
 
 
Fields Descriptions:
Field Required Description
channelId Yes A unique ID that identifies channel.
To be shared by Bills Payment Engine Admins
spCode Yes A unique ID that identifies biller (or service provider). 
To be retrieved through Biller Retrieval API.
requestType Yes A String that identifies type of the request, can be inquiry, 
payments, statusCheck etc.
timestamp Yes Indicate date and time on which the channel posted a request to 
the Engine
userId Yes User ID of the channel user, can be Teller ID, Customer Unique 
Identifier and/or Agent ID etc.
branchCode Yes Indicate the branch code where the user belongs to
22
Secret
billRef Yes This is the bill reference number that identifies specific bill of a 
certain biller (customer needs to have this before initiating a 
bills payment)
statusCode Indicate whether the response is success of failure
message Show engine messages in relation to the status code, CBS and 
and biller response
serviceName This indicates what service does the biller offer to the customer
description Description detailing the service offered to the customer by the 
service provider
billCreatedAt Date on which the bill was created
totalAmount Bill total amount
balance Bill balance amount, can be useful to some biller who support 
Partial payments for their bills
currency Currency of the amount (currently only TZS is supported)
phoneNumber Billed customer’s phone number
email Billed customer’s email address
billedName Billed customer’s full name
paymentMode Mode on which a customer can pay the bill:
Assume Julius Nyerere is billed 9000/= TZS.
- Partial 
May be paid in single or multiple installments, with the last 
instalment greater or equal to the remaining billed amount.
JK is allowed to partially pay the bill; hence, he can then pay 
500, then 4500 and finally 3500 (which in this case if you 
sum all instalments, total paid amount exceed the bill 
amount by 500).
- Full 
Shall be paid in one installment with the amount paid equal 
or greater than the billed amount.
JK can only pay once, hence is allowed to pay 9000/= or 
more.
- Exact 
Shall be paid in one installment with the amount paid being 
equal to the billed amount.
 
JK is allowed to only pay 9000/= nothing more, nothing less.
- Limited 
May be paid in single or multiple installments, with the last 
instalment equal to the remaining billed amount.
JK can pay 500, then 4500 and finally complete/pay the 
remaining 3000
23
Secret
- Infinity 
Shall be paid with any amount greater than zero, at infinity 
installments.
JK can pay 500, then 4500, then 500,000 etc.
expiryDate The date on which the bill will expire, customer is not allowed to 
pay the bill beyond this date.
creditAccount Masked biller/credit account, the account into which the bill 
paid amount will be credited to
creditCurrency Currency of the credit account
extraFields This field allow the gateway to pass extra fields that are unique 
to bill / biller and that cannot be accommodated on the normal 
fields provided.
approach Y Approach on which the request is treated:
Possible values:
- Async (preferred approach)
Channel send request to the engine, engine validate and 
acknowledge to the channel, session is ended right there. 
Then engine will proceed with other processes and update 
channel by posting response through the callbackUrl
- Sync
Channel send request to the engine, engine validate, and 
proceed with all processes, once done, respond to the 
channel with final response.
callbackUrl Y An API / service exposed by the channel backend system, that 
receive JSON request payload from the engine. Engine will use 
this to update on the status of the payload. The channel should 
check statusCode to along with other details to determine 
whether the bill has been processed successfully or not. 
For the case of sync approach, value of this field should be 
empty string otherwise, needs to be a valid API endpoint.
amount Y Amount paid; hence this is the amount to be debited from 
customers account and credited to the biller
debitAccount Y Customer’s account (or rather the account to be debited)
debitCurrency Y Debit account currency
paymentType Y Type of payment, possible values are:
- Account (Preferred option)
This indicate that the customer is paying the bill through 
his/her account.
- Cash
Indicating that the customer is paying the bill by using 
cash/cheque etc.
24
Secret
This is applicable to Branch and Agency Banking systems.
channelCode Y Code that identifies channel
Possible values are:
- IOS_APP
- ANDROID_APP
- USSD
- IB (i.e., Retail Internet Banking)
- OBDX (i.e., NBC Direct or Corporate Internet Banking) 
- CBP
- DABP (i.e., Direct Agency Banking)
- IABP (i.e., Indirect Agency Banking)
payerName Y This is the payer. i.e., the person who paid the bill. Not 
necessarily the billed customer
payerPhone Y Phone number of the payer
payerEmail Y Email address of the payer
narration Y Narration or remarks from the customer/channel
inquiryRawResponse Y Stringified inquiry raw response. i.e., stringify the corresponding 
inquiry response you received for the bill.
gatewayRef Y Unique reference generated by the engine
transactionTime Y Date and Time on which the transactions were processed
billerReceipt N Receipt number received from the biller (if any). Please note, 
some billers do not return these receipt numbers, in this case 
gatewayRef can be used as receipt number.
remarks N Remarks from the biller (if any)
accountingStatus Status indicating that the accounting has been completed 
successfully.
billerNotified Indicate whether the biller has been notified or not.
Possible values:
- Completed: 
Biller has been notified.
- InProgress:
Notification to biller is in progress.
RESPONSE CODES 
Describes possible response codes and their meaning
Code Description 
600 Success
601 Possible duplicate transaction
602 Validation Failed
603 Duplicate Transaction
604 No Response From Third Party
609 Authentication Fail
610 Client Not Registered
25
Secret
612 Requested Service In Unavailable
613 Unauthorized To Access Requested Service
615 Biller Does Not Exists
636 Biller Is Currently Disabled
699 Exception Caught