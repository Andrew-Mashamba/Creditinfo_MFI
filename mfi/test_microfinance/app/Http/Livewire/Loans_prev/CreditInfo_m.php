<?php

namespace App\Http\Livewire\Loans;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use nusoap_client;
use SimpleXMLElement;
use Symfony\Component\Mime\Crypto\SMimeSigner;

class CreditInfo_m extends Component
{

    public $currentStep;
    public $showDialog=true;

    public $selectedHeader;

    public $selectedMenu;
    public $responseXml;

    public $show=false;

    public $customerBodyInfo;

    public $table_headers=[];

    public $client_nida_number;

    public function render()
    {
        $this->sendSoapRequest();
        return view('livewire.loans.credit-info');
    }



    function uuid_create() {

        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)

        );
    }




    public function sendSoapRequest()
    {

        $client=DB::table('clients')->where('client_number',session()->get('currentloanClient'))->first();

        $client_name=$client->first_name.' '.$client->middle_name.' '.$client->last_name;



        if(strlen($client->national_id)>=20){
            $nida_number=substr($client->national_id,0,8).'-'.substr($client->national_id,8,5).'-'.substr($client->national_id,13,5).'-'.substr($client->national_id,18,2);

        }else{

            $nida_number=$client->national_id;
           // echo "invalid nida number";

        }

        $nida_number='19850723-14125-00004-24';



        $this->show=false;

        $xml='<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
    <s:Header>
        <wsse:Security s:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
            <wsse:UsernameToken wsu:Id="ad2b9f33-eba3-4e0f-ae41-e90379b97f56" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
                <wsse:Username>isale</wsse:Username>
                <wsse:Password>20ISALe24</wsse:Password>
            </wsse:UsernameToken>
        </wsse:Security>
    </s:Header>
    <s:Body>

        <Query xmlns="http://creditinfo.com/schemas/2012/09/MultiConnector">
            <request xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
                <MessageId>' . uuid_create() . '</MessageId>
                <RequestXml>
                    <RequestXml xmlns="http://creditinfo.com/schemas/2012/09/MultiConnector/Messages/Request">
                        <connector id="22636b5b-067f-48ae-86f6-cf6a900b7408">
                            <data id="' . uuid_create() . '">
							<request xmlns="http://creditinfo.com/schemas/2012/09/MultiConnector/Connectors/Bee/Request"
								 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
								 xsi:schemaLocation="http://creditinfo.com/schemas/2012/09/MultiConnector/Connectors/Bee/Request file:/C:/Users/d.felix/Desktop/Smart%20Search/Smart%20Search/TZA_NMB_BeeRequest.xsd">
								    <DecisionWorkflow>NMB.TZA.Base</DecisionWorkflow>
								    <RequestData>
								        <Individual>
								        <DateOfBirth>1996-01-10T21:00:00Z</DateOfBirth>
								        <FullName>'.$client_name.'</FullName>
								          <IdNumbers>
								          	<IdNumberPairIndividual>
								               	<IdNumber>'.$nida_number.'</IdNumber>
								               	<IdNumberType>NationalID</IdNumberType>
								                </IdNumberPairIndividual>
								          </IdNumbers>
								       	<PhoneNumbers>
                                    				<string></string>
                                 				</PhoneNumbers>
								            <InquiryReasons>ApplicationForCreditOrAmendmentOfCreditTerms</InquiryReasons>
								            <CreditInfoId></CreditInfoId>
								            <TypeOfReport>CreditinfoReportPlus</TypeOfReport>
								        </Individual>
								    </RequestData>
							</request>
                            </data>
                        </connector>
                    </RequestXml>
                </RequestXml>
                <Timeout i:nil="true"/>
            </request>
        </Query>
    </s:Body>
</s:Envelope>';



        $endpoint = "https://mc-stage.creditinfo.co.tz/MultiConnector.svc";
        $soapAction = "http://creditinfo.com/schemas/2012/09/MultiConnector/MultiConnectorService/Query";
        $xml = simplexml_load_string($xml);



        try {
            $client = new Client();
            $options = [
                'body'    => $xml->asXML(),
                'headers' => [
                    "Content-Type"      => "text/xml",
                    "Accept"            => "*/*",
                    "Accept-Encoding"   => "gzip, deflate, br",
                    "Host"              => "mc-stage.creditinfo.co.tz",
                    "SOAPAction"        => $soapAction,
                ],
            ];

            $response = $client->post($endpoint, $options);

            $content = $response->getBody()->getContents();

            $cleanedXml = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $content);

            $xml = new SimpleXMLElement($cleanedXml);

            //$body = $xml->xpath('//soapBody')[0];

            $array = json_decode(json_encode((array)$xml), TRUE);


            // Accessing values
            $messageId = $array['sBody']['QueryResponse']['QueryResult']['MessageId'];
            $timestamp = $array['sBody']['QueryResponse']['QueryResult']['Timestamp'];



            if (
                isset($array['sBody']['QueryResponse']['QueryResult']['ResponseXml']['response']['connector']['notFound'])
            ) {
                $this->responseXml = $array['sBody']['QueryResponse']['QueryResult']['ResponseXml']['response']['connector'];


            } else {
                // Path does not exist
                $this->responseXml = $array['sBody']['QueryResponse']['QueryResult']['ResponseXml']['response']['connector']['data']['response']['CustomReport'];

            }






        } catch (\GuzzleHttp\Exception\RequestException $e) {
            dd("Request Error", [$e->getMessage()]);
        }

        catch (\Exception $e) {
            dd("Error", [$e->getMessage()]);
        }





    }



    public function CustomerInfo($value)
    {
        $this->customerBodyInfo=null;
        $this->table_headers=$value[1];
        $this->selectedMenu=$value[0];

    }

    public function secondCustomerInfo($data)
    {
        $this->customerBodyInfo=$data[1];
        $this->selectedHeader=$data[0];
    }



    public function bureauCheck()
    {
        $this->currentStep=2;
    }

    public function creditInforResponse()
    {
        $xml = <<<XML
        <s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
   <s:Body>
      <QueryResponse xmlns="http://creditinfo.com/schemas/2012/09/MultiConnector">
         <QueryResult xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
            <MessageId>a8395472-fd63-4998-b1fe-7b9840104331</MessageId>
            <ResponseXml>
               <response xmlns="http://creditinfo.com/schemas/2012/09/MultiConnector/Messages/Response">
                  <connector id="connectorID">
                     <data id="dataID">
                        <response xmlns="http://creditinfo.com/schemas/2012/09/MultiConnector/Connectors/Bee/Response">
                           <CustomReport>
                              <BouncedCheques/>
                              <Branches>
                                 <NumberOfBranches>0</NumberOfBranches>
                              </Branches>
                              <CIP>
                                 <RecordList>
                                    <Record>
                                       <Date>2023-10-10T13:17:38.8040856Z</Date>
                                       <Grade>XX</Grade>
                                       <ProbabilityOfDefault>100</ProbabilityOfDefault>
                                       <ReasonsList>
                                          <Reason>
                                             <Code>XNOF</Code>
                                             <Description>Subject is not found in database</Description>
                                          </Reason>
                                          <Reason>
                                             <Code>XNOC</Code>
                                             <Description>No contracts in the database</Description>
                                          </Reason>
                                          <Reason>
                                             <Code>XDAT</Code>
                                             <Description>Subject did not have any snapshots in last 36 months</Description>
                                          </Reason>
                                       </ReasonsList>
                                       <Score>999</Score>
                                       <Trend>NoChange</Trend>
                                    </Record>
                                    <Record>
                                       <Date>2023-09-29T21:00:00Z</Date>
                                       <Grade>XX</Grade>
                                       <ProbabilityOfDefault>100</ProbabilityOfDefault>
                                       <ReasonsList>
                                          <Reason>
                                             <Code>XNOF</Code>
                                          </Reason>
                                          <Reason>
                                             <Code>XNOC</Code>
                                          </Reason>
                                          <Reason>
                                             <Code>XDAT</Code>
                                          </Reason>
                                       </ReasonsList>
                                       <Score>999</Score>
                                       <Trend>NoChange</Trend>
                                    </Record>
                                    <Record>
                                       <Date>2023-08-30T21:00:00Z</Date>
                                       <Grade>XX</Grade>
                                       <ProbabilityOfDefault>100</ProbabilityOfDefault>
                                       <ReasonsList>
                                          <Reason>
                                             <Code>XNOF</Code>
                                          </Reason>
                                          <Reason>
                                             <Code>XNOC</Code>
                                          </Reason>
                                          <Reason>
                                             <Code>XDAT</Code>
                                          </Reason>
                                       </ReasonsList>
                                       <Score>999</Score>
                                       <Trend>NoChange</Trend>
                                    </Record>
                                    <Record>
                                       <Date>2023-07-30T21:00:00Z</Date>
                                       <Grade>XX</Grade>
                                       <ProbabilityOfDefault>100</ProbabilityOfDefault>
                                       <ReasonsList>
                                          <Reason>
                                             <Code>XNOF</Code>
                                          </Reason>
                                          <Reason>
                                             <Code>XNOC</Code>
                                          </Reason>
                                          <Reason>
                                             <Code>XDAT</Code>
                                          </Reason>
                                       </ReasonsList>
                                       <Score>999</Score>
                                       <Trend>NoChange</Trend>
                                    </Record>
                                    <Record>
                                       <Date>2023-06-29T21:00:00Z</Date>
                                       <Grade>XX</Grade>
                                       <ProbabilityOfDefault>100</ProbabilityOfDefault>
                                       <ReasonsList>
                                          <Reason>
                                             <Code>XNOF</Code>
                                          </Reason>
                                          <Reason>
                                             <Code>XNOC</Code>
                                          </Reason>
                                          <Reason>
                                             <Code>XDAT</Code>
                                          </Reason>
                                       </ReasonsList>
                                       <Score>999</Score>
                                       <Trend>NoChange</Trend>
                                    </Record>
                                    <Record>
                                       <Date>2023-05-30T21:00:00Z</Date>
                                       <Grade>XX</Grade>
                                       <ProbabilityOfDefault>100</ProbabilityOfDefault>
                                       <ReasonsList>
                                          <Reason>
                                             <Code>XNOF</Code>
                                          </Reason>
                                          <Reason>
                                             <Code>XNOC</Code>
                                          </Reason>
                                          <Reason>
                                             <Code>XDAT</Code>
                                          </Reason>
                                       </ReasonsList>
                                       <Score>999</Score>
                                       <Trend>NoChange</Trend>
                                    </Record>
                                    <Record>
                                       <Date>2023-04-29T21:00:00Z</Date>
                                       <Grade>XX</Grade>
                                       <ProbabilityOfDefault>100</ProbabilityOfDefault>
                                       <ReasonsList>
                                          <Reason>
                                             <Code>XNOF</Code>
                                          </Reason>
                                          <Reason>
                                             <Code>XNOC</Code>
                                          </Reason>
                                          <Reason>
                                             <Code>XDAT</Code>
                                          </Reason>
                                       </ReasonsList>
                                       <Score>999</Score>
                                       <Trend>NoChange</Trend>
                                    </Record>
                                    <Record>
                                       <Date>2023-03-30T21:00:00Z</Date>
                                       <Grade>XX</Grade>
                                       <ProbabilityOfDefault>100</ProbabilityOfDefault>
                                       <ReasonsList>
                                          <Reason>
                                             <Code>XNOF</Code>
                                          </Reason>
                                          <Reason>
                                             <Code>XNOC</Code>
                                          </Reason>
                                          <Reason>
                                             <Code>XDAT</Code>
                                          </Reason>
                                       </ReasonsList>
                                       <Score>999</Score>
                                       <Trend>NoChange</Trend>
                                    </Record>
                                    <Record>
                                       <Date>2023-02-27T21:00:00Z</Date>
                                       <Grade>XX</Grade>
                                       <ProbabilityOfDefault>100</ProbabilityOfDefault>
                                       <ReasonsList>
                                          <Reason>
                                             <Code>XNOF</Code>
                                          </Reason>
                                          <Reason>
                                             <Code>XNOC</Code>
                                          </Reason>
                                          <Reason>
                                             <Code>XDAT</Code>
                                          </Reason>
                                       </ReasonsList>
                                       <Score>999</Score>
                                       <Trend>NoChange</Trend>
                                    </Record>
                                    <Record>
                                       <Date>2023-01-30T21:00:00Z</Date>
                                       <Grade>XX</Grade>
                                       <ProbabilityOfDefault>100</ProbabilityOfDefault>
                                       <ReasonsList>
                                          <Reason>
                                             <Code>XNOF</Code>
                                          </Reason>
                                          <Reason>
                                             <Code>XNOC</Code>
                                          </Reason>
                                          <Reason>
                                             <Code>XDAT</Code>
                                          </Reason>
                                       </ReasonsList>
                                       <Score>999</Score>
                                       <Trend>NoChange</Trend>
                                    </Record>
                                    <Record>
                                       <Date>2022-12-30T21:00:00Z</Date>
                                       <Grade>XX</Grade>
                                       <ProbabilityOfDefault>100</ProbabilityOfDefault>
                                       <ReasonsList>
                                          <Reason>
                                             <Code>XNOF</Code>
                                          </Reason>
                                          <Reason>
                                             <Code>XNOC</Code>
                                          </Reason>
                                          <Reason>
                                             <Code>XDAT</Code>
                                          </Reason>
                                       </ReasonsList>
                                       <Score>999</Score>
                                       <Trend>NoChange</Trend>
                                    </Record>
                                    <Record>
                                       <Date>2022-11-29T21:00:00Z</Date>
                                       <Grade>XX</Grade>
                                       <ProbabilityOfDefault>100</ProbabilityOfDefault>
                                       <ReasonsList>
                                          <Reason>
                                             <Code>XNOF</Code>
                                          </Reason>
                                          <Reason>
                                             <Code>XNOC</Code>
                                          </Reason>
                                          <Reason>
                                             <Code>XDAT</Code>
                                          </Reason>
                                       </ReasonsList>
                                       <Score>999</Score>
                                       <Trend>NoChange</Trend>
                                    </Record>
                                    <Record>
                                       <Date>2022-10-30T21:00:00Z</Date>
                                       <Grade>XX</Grade>
                                       <ProbabilityOfDefault>100</ProbabilityOfDefault>
                                       <ReasonsList>
                                          <Reason>
                                             <Code>XNOF</Code>
                                          </Reason>
                                          <Reason>
                                             <Code>XNOC</Code>
                                          </Reason>
                                          <Reason>
                                             <Code>XDAT</Code>
                                          </Reason>
                                       </ReasonsList>
                                       <Score>999</Score>
                                       <Trend>Up</Trend>
                                    </Record>
                                 </RecordList>
                              </CIP>
                              <CIQ>
                                 <Detail>
                                    <LostStolenRecordsFound>0</LostStolenRecordsFound>
                                    <NumberOfCancelledClosedContracts>0</NumberOfCancelledClosedContracts>
                                    <NumberOfSubscribersMadeInquiriesLast14Days>0</NumberOfSubscribersMadeInquiriesLast14Days>
                                    <NumberOfSubscribersMadeInquiriesLast2Days>0</NumberOfSubscribersMadeInquiriesLast2Days>
                                 </Detail>
                                 <Summary>
                                    <NumberOfFraudAlertsPrimarySubject>0</NumberOfFraudAlertsPrimarySubject>
                                    <NumberOfFraudAlertsThirdParty>0</NumberOfFraudAlertsThirdParty>
                                 </Summary>
                              </CIQ>
                              <ContractOverview/>
                              <ContractSummary>
                                 <AffordabilityHistoryList>
                                    <AffordabilityHistory>
                                       <Month>10</Month>
                                       <MonthlyAffordability>
                                          <Currency>TZS</Currency>
                                          <LocalValue>0</LocalValue>
                                          <Value>0</Value>
                                       </MonthlyAffordability>
                                       <Year>2023</Year>
                                    </AffordabilityHistory>
                                    <AffordabilityHistory>
                                       <Month>9</Month>
                                       <MonthlyAffordability>
                                          <Currency>TZS</Currency>
                                          <LocalValue>0</LocalValue>
                                          <Value>0</Value>
                                       </MonthlyAffordability>
                                       <Year>2023</Year>
                                    </AffordabilityHistory>
                                    <AffordabilityHistory>
                                       <Month>8</Month>
                                       <MonthlyAffordability>
                                          <Currency>TZS</Currency>
                                          <LocalValue>0</LocalValue>
                                          <Value>0</Value>
                                       </MonthlyAffordability>
                                       <Year>2023</Year>
                                    </AffordabilityHistory>
                                    <AffordabilityHistory>
                                       <Month>7</Month>
                                       <MonthlyAffordability>
                                          <Currency>TZS</Currency>
                                          <LocalValue>0</LocalValue>
                                          <Value>0</Value>
                                       </MonthlyAffordability>
                                       <Year>2023</Year>
                                    </AffordabilityHistory>
                                    <AffordabilityHistory>
                                       <Month>6</Month>
                                       <MonthlyAffordability>
                                          <Currency>TZS</Currency>
                                          <LocalValue>0</LocalValue>
                                          <Value>0</Value>
                                       </MonthlyAffordability>
                                       <Year>2023</Year>
                                    </AffordabilityHistory>
                                    <AffordabilityHistory>
                                       <Month>5</Month>
                                       <MonthlyAffordability>
                                          <Currency>TZS</Currency>
                                          <LocalValue>0</LocalValue>
                                          <Value>0</Value>
                                       </MonthlyAffordability>
                                       <Year>2023</Year>
                                    </AffordabilityHistory>
                                    <AffordabilityHistory>
                                       <Month>4</Month>
                                       <MonthlyAffordability>
                                          <Currency>TZS</Currency>
                                          <LocalValue>0</LocalValue>
                                          <Value>0</Value>
                                       </MonthlyAffordability>
                                       <Year>2023</Year>
                                    </AffordabilityHistory>
                                    <AffordabilityHistory>
                                       <Month>3</Month>
                                       <MonthlyAffordability>
                                          <Currency>TZS</Currency>
                                          <LocalValue>0</LocalValue>
                                          <Value>0</Value>
                                       </MonthlyAffordability>
                                       <Year>2023</Year>
                                    </AffordabilityHistory>
                                    <AffordabilityHistory>
                                       <Month>2</Month>
                                       <MonthlyAffordability>
                                          <Currency>TZS</Currency>
                                          <LocalValue>0</LocalValue>
                                          <Value>0</Value>
                                       </MonthlyAffordability>
                                       <Year>2023</Year>
                                    </AffordabilityHistory>
                                    <AffordabilityHistory>
                                       <Month>1</Month>
                                       <MonthlyAffordability>
                                          <Currency>TZS</Currency>
                                          <LocalValue>0</LocalValue>
                                          <Value>0</Value>
                                       </MonthlyAffordability>
                                       <Year>2023</Year>
                                    </AffordabilityHistory>
                                    <AffordabilityHistory>
                                       <Month>12</Month>
                                       <MonthlyAffordability>
                                          <Currency>TZS</Currency>
                                          <LocalValue>0</LocalValue>
                                          <Value>0</Value>
                                       </MonthlyAffordability>
                                       <Year>2022</Year>
                                    </AffordabilityHistory>
                                    <AffordabilityHistory>
                                       <Month>11</Month>
                                       <MonthlyAffordability>
                                          <Currency>TZS</Currency>
                                          <LocalValue>0</LocalValue>
                                          <Value>0</Value>
                                       </MonthlyAffordability>
                                       <Year>2022</Year>
                                    </AffordabilityHistory>
                                 </AffordabilityHistoryList>
                                 <AffordabilitySummary>
                                    <MonthlyAffordability>
                                       <Currency>TZS</Currency>
                                       <LocalValue>0</LocalValue>
                                       <Value>0</Value>
                                    </MonthlyAffordability>
                                 </AffordabilitySummary>
                                 <Debtor>
                                    <ClosedContracts>0</ClosedContracts>
                                    <OpenContracts>0</OpenContracts>
                                 </Debtor>
                                 <Guarantor>
                                    <ClosedContracts>0</ClosedContracts>
                                    <OpenContracts>0</OpenContracts>
                                 </Guarantor>
                                 <Overall>
                                    <MaxDueInstallmentsClosedContracts>0</MaxDueInstallmentsClosedContracts>
                                    <MaxDueInstallmentsOpenContracts>0</MaxDueInstallmentsOpenContracts>
                                    <WorstPastDueAmount>
                                       <Currency>TZS</Currency>
                                       <LocalValue>0</LocalValue>
                                       <Value>0</Value>
                                    </WorstPastDueAmount>
                                    <WorstPastDueDays>0</WorstPastDueDays>
                                 </Overall>
                              </ContractSummary>
                              <Contracts/>
                              <CurrentRelations/>
                              <Dashboard>
                                 <CIP>
                                    <Grade>XX</Grade>
                                    <Score>999</Score>
                                 </CIP>
                                 <CIQ>
                                    <FraudAlerts>0</FraudAlerts>
                                    <FraudAlertsThirdParty>0</FraudAlertsThirdParty>
                                 </CIQ>
                                 <Collaterals>
                                    <HighestCollateralValueType>NotSpecified</HighestCollateralValueType>
                                    <NumberOfCollaterals>0</NumberOfCollaterals>
                                 </Collaterals>
                                 <Disputes>
                                    <ActiveContractDisputes>0</ActiveContractDisputes>
                                    <ActiveSubjectDisputes>2</ActiveSubjectDisputes>
                                    <ClosedContractDisputes>0</ClosedContractDisputes>
                                    <ClosedSubjectDisputes>0</ClosedSubjectDisputes>
                                 </Disputes>
                                 <Inquiries>
                                    <InquiriesForLast12Months>25</InquiriesForLast12Months>
                                 </Inquiries>
                                 <PaymentsProfile>
                                    <WorstPastDueDaysCurrent>0</WorstPastDueDaysCurrent>
                                    <WorstPastDueDaysForLast12Months>0</WorstPastDueDaysForLast12Months>
                                 </PaymentsProfile>
                                 <Relations>
                                    <NumberOfRelations>0</NumberOfRelations>
                                 </Relations>
                              </Dashboard>
                              <Disputes>
                                 <Summary>
                                    <NumberOfActiveDisputesContracts>0</NumberOfActiveDisputesContracts>
                                    <NumberOfActiveDisputesSubjectData>2</NumberOfActiveDisputesSubjectData>
                                    <NumberOfClosedDisputesContracts>0</NumberOfClosedDisputesContracts>
                                    <NumberOfClosedDisputesSubjectData>0</NumberOfClosedDisputesSubjectData>
                                    <NumberOfFalseDisputes>0</NumberOfFalseDisputes>
                                 </Summary>
                              </Disputes>
                              <Individual>
                                 <Contact>
                                    <MobilePhone>+255754509579</MobilePhone>
                                 </Contact>
                                 <General>
                                    <BirthSurname>MBUYA</BirthSurname>
                                    <Citizenship>NotSpecified</Citizenship>
                                    <ClassificationOfIndividual>Individual</ClassificationOfIndividual>
                                    <CountryOfBirth>NotSpecified</CountryOfBirth>
                                    <DateOfBirth>1985-07-22T21:00:00Z</DateOfBirth>
                                    <Education>NotSpecified</Education>
                                    <Employment>NotSpecified</Employment>
                                    <FirstName>JOSEPH</FirstName>
                                    <FullName>JOSEPH  MBUYA</FullName>
                                    <Gender>NotSpecified</Gender>
                                    <MaritalStatus>NotSpecified</MaritalStatus>
                                    <Nationality>TZ</Nationality>
                                    <NegativeStatus>NotSpecified</NegativeStatus>
                                 </General>
                                 <Identifications>
                                    <NationalID>19850723-14125-00004-24</NationalID>
                                    <PassportIssuerCountry>NotSpecified</PassportIssuerCountry>
                                 </Identifications>
                                 <MainAddress>
                                    <AddressLine>Tanzania</AddressLine>
                                    <Country>NotSpecified</Country>
                                 </MainAddress>
                                 <SecondaryAddress>
                                    <Country>NotSpecified</Country>
                                 </SecondaryAddress>
                              </Individual>
                              <Inquiries>
                                 <InquiryList>
                                    <Inquiry>
                                       <DateOfInquiry>2023-03-21T13:44:16.267Z</DateOfInquiry>
                                       <Product>WS Creditinfo Report Plus</Product>
                                       <Reason>1</Reason>
                                       <Sector>Others</Sector>
                                       <Subscriber>Y9Bank</Subscriber>
                                    </Inquiry>
                                    <Inquiry>
                                       <DateOfInquiry>2023-03-21T12:19:23.807Z</DateOfInquiry>
                                       <Product>WS Creditinfo Report Plus</Product>
                                       <Reason>1</Reason>
                                       <Sector>Others</Sector>
                                       <Subscriber>Y9Bank</Subscriber>
                                    </Inquiry>
                                    <Inquiry>
                                       <DateOfInquiry>2023-01-26T07:04:55.347Z</DateOfInquiry>
                                       <Product>WS Creditinfo Report Plus</Product>
                                       <Reason>1</Reason>
                                       <Sector>Banks</Sector>
                                       <Subscriber>SELCOM</Subscriber>
                                    </Inquiry>
                                    <Inquiry>
                                       <DateOfInquiry>2023-01-20T13:46:13.427Z</DateOfInquiry>
                                       <Product>WS Creditinfo Report Plus</Product>
                                       <Reason>1</Reason>
                                       <Sector>Others</Sector>
                                       <Subscriber>Y9Bank</Subscriber>
                                    </Inquiry>
                                    <Inquiry>
                                       <DateOfInquiry>2023-01-17T08:11:23.53Z</DateOfInquiry>
                                       <Product>WS Creditinfo Report Plus</Product>
                                       <Reason>1</Reason>
                                       <Sector>Banks</Sector>
                                       <Subscriber>SELCOM</Subscriber>
                                    </Inquiry>
                                 </InquiryList>
                                 <Summary>
                                    <NumberOfInquiriesLast12Months>25</NumberOfInquiriesLast12Months>
                                    <NumberOfInquiriesLast1Month>0</NumberOfInquiriesLast1Month>
                                    <NumberOfInquiriesLast24Months>25</NumberOfInquiriesLast24Months>
                                    <NumberOfInquiriesLast3Months>0</NumberOfInquiriesLast3Months>
                                    <NumberOfInquiriesLast6Months>0</NumberOfInquiriesLast6Months>
                                 </Summary>
                              </Inquiries>
                              <Managers>
                                 <NumberOfManagers>0</NumberOfManagers>
                              </Managers>
                              <Parameters>
                                 <Consent>true</Consent>
                                 <IDNumber>315972954</IDNumber>
                                 <IDNumberType>CreditinfoId</IDNumberType>
                                 <InquiryReason>ApplicationForCreditOrAmendmentOfCreditTerms</InquiryReason>
                                 <Sections>
                                    <string>CreditinfoReportPlus</string>
                                 </Sections>
                                 <SubjectType>Individual</SubjectType>
                              </Parameters>
                              <ReportInfo>
                                 <Created>2023-10-10T13:17:40.2032992Z</Created>
                                 <ReferenceNumber>2691928-315972954</ReferenceNumber>
                                 <RequestedBy>NMB SMARTTEST</RequestedBy>
                                 <Subscriber>NMBPLC</Subscriber>
                              </ReportInfo>
                              <Shareholders>
                                 <NumberOfShareholders>0</NumberOfShareholders>
                              </Shareholders>
                              <SubjectInfoHistory>
                                 <GeneralList>
                                    <General>
                                       <Item>FullName</Item>
                                       <Subscriber>B01</Subscriber>
                                       <ValidFrom>2021-09-29T21:00:00Z</ValidFrom>
                                       <ValidTo>2021-11-29T21:00:00Z</ValidTo>
                                       <Value>JOSEPH  MBUYA</Value>
                                    </General>
                                    <General>
                                       <Item>FullName</Item>
                                       <Subscriber>B01</Subscriber>
                                       <ValidFrom>2021-11-29T21:00:00Z</ValidFrom>
                                       <ValidTo>2022-03-30T21:00:00Z</ValidTo>
                                       <Value>JOSEPH   MBUYA</Value>
                                    </General>
                                 </GeneralList>
                              </SubjectInfoHistory>
                           </CustomReport>
                           <MultiHit>
                              <message>SingleHit + Report</message>
                           </MultiHit>
                        </response>
                     </data>
                  </connector>
               </response>
            </ResponseXml>
            <Timestamp>2023-10-10T13:17:40.8954032Z</Timestamp>
         </QueryResult>
      </QueryResponse>
   </s:Body>
</s:Envelope>
XML;

        $dom = new DOMDocument();
        $dom->loadXML($xml);



        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('s', 'http://schemas.xmlsoap.org/soap/envelope/');
        $xpath->registerNamespace('ci', 'http://creditinfo.com/schemas/2012/09/MultiConnector');
        $xpath->registerNamespace('r', 'http://creditinfo.com/schemas/2012/09/MultiConnector/Messages/Response');
        $xpath->registerNamespace('ci2', 'http://creditinfo.com/schemas/2012/09/MultiConnector/Connectors/Bee/Response');


        // XPath expression to select the Individual element
        $individualXPath = '//s:Envelope/s:Body/ci:QueryResponse/ci:QueryResult/ci:ResponseXml/r:response/r:connector/r:data/ci2:response/ci2:CustomReport/ci2:Individual';


        dd($xpath->query($individualXPath)->item(0));

        // Query the Individual element
        $individual = $xpath->query($individualXPath)->item(0);

        dd($individual);


        return $individual;


    }

}
