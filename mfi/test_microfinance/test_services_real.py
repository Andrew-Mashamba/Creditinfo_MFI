#!/usr/bin/env python3
"""
API Service Test Suite with REAL Request/Response Structures
Based on actual controller validations and service implementations
"""

import json
import time
import requests
import argparse
import os
import sys
from datetime import datetime, timedelta
from typing import Dict, List, Any, Optional
from dataclasses import dataclass, asdict
from enum import Enum
from tabulate import tabulate
from colorama import init, Fore, Back, Style
import urllib3

# Disable SSL warnings for local testing
urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

# Initialize colorama
init(autoreset=True)

class TestStatus(Enum):
    SUCCESS = "SUCCESS"
    FAILED = "FAILED"
    ERROR = "ERROR"
    SKIPPED = "SKIPPED"
    TIMEOUT = "TIMEOUT"

@dataclass
class TestResult:
    service_name: str
    endpoint: str
    status: TestStatus
    http_code: int
    duration_ms: float
    request_id: Optional[str]
    request_data: Dict
    response_data: Optional[Dict]
    expected_response: Dict
    error_message: Optional[str]
    timestamp: str

class RealServiceTester:
    """
    Service tester with actual request/response structures from the codebase
    """
    
    def __init__(self, base_url: str = "http://127.0.0.1:8000", environment: str = "local"):
        self.base_url = base_url
        self.environment = environment
        self.results: List[TestResult] = []
        self.session = requests.Session()
        self.session.verify = False
        self.api_key = self._get_api_key(environment)
        self.session.headers.update({
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-API-Key': self.api_key,
            'User-Agent': 'RealServiceTestSuite/3.0'
        })
        
        os.makedirs('test_results', exist_ok=True)
        self.test_data = self._load_real_test_data()
        self.expected_responses = self._load_expected_responses()
        
    def _get_api_key(self, environment: str) -> str:
        keys = {
            'local': 'local_test_api_key',
            'staging': 'staging_api_key',
            'production': 'production_api_key'
        }
        return keys.get(environment, 'test_api_key')
    
    def _load_real_test_data(self) -> Dict:
        """
        Load REAL test data based on actual controller validations
        """
        timestamp = int(time.time())
        
        return {
            # From ServiceTestController validation rules
            'internal_funds_transfer': {
                'source_account': '06012040022',  # Required string
                'destination_account': '06012040023',  # Required string
                'amount': 10000.00,  # Required numeric min:0.01
                'currency': 'TZS',  # Optional string size:3
                'description': f'Test internal transfer at {datetime.now()}',  # Optional string max:255
                'reference': f'TEST_INT_{timestamp}',  # Optional string
                'beneficiary_name': 'John Doe Test',  # Optional string
                'sender_name': 'Jane Smith Test'  # Optional string - added for service compatibility
            },
            
            'external_funds_transfer': {
                'source_account': '06012040022',  # Required string
                'destination_account': '1234567890',  # Required string
                'destination_bank': 'CRDB',  # Required string
                'swift_code': 'CORUTZTZ',  # Optional string
                'amount': 50000.00,  # Required numeric min:0.01
                'currency': 'TZS',  # Optional string size:3
                'beneficiary_name': 'Jane Smith External',  # Required string
                'description': f'Test external transfer at {datetime.now()}',  # Optional string max:255
                'reference': f'TEST_EXT_{timestamp}',  # Optional string
                'sender_name': 'John Sender Test'  # Optional string - added for service compatibility
            },
            
            'mini_statement': {
                'account_number': '06012040022',  # Required string
                'limit': 5,  # Optional integer min:1 max:10
                'channel_code': 'SACCOSNBC'  # Optional string
            },
            
            'full_statement': {
                'account_number': '06012040022',  # Required string
                'from_date': (datetime.now() - timedelta(days=30)).strftime('%Y-%m-%d'),  # Required date
                'to_date': datetime.now().strftime('%Y-%m-%d'),  # Required date after_or_equal:from_date
                'format': 'json',  # Optional in:json,pdf,excel
                'channel_code': 'SACCOSNBC'  # Optional string
            },
            
            'account_lookup': {
                'account_number': '06012040022',  # One of these required
                # 'phone_number': '255700000000',  # Optional string
                # 'id_number': 'ID123456',  # Optional string
                # 'email': 'test@example.com',  # Optional email
                'channel_code': 'SACCOSNBC'  # Optional string
            },
            
            'utilities': {
                'utility_type': 'electricity',  # Required in:water,electricity,gas,internet,tv
                'account_number': '06012040022',  # Required string
                'meter_number': 'LUKU123456',  # Optional string
                'amount': 20000.00,  # Required numeric min:0.01
                'customer_name': 'Test Customer',  # Optional string
                'phone_number': '255700000000',  # Optional string
                'provider_code': 'TANESCO'  # Required string
            },
            
            'sms': {
                'phone_numbers': ['255700000001', '255700000002'],  # Required array
                'message': f'Test SMS from API test at {datetime.now().strftime("%H:%M:%S")}',  # Required string max:160
                'sender_id': 'SACCOS',  # Optional string max:11
                'schedule_time': None,  # Optional date
                'sms_type': 'TRANSACTIONAL'  # Optional in:TRANSACTIONAL,PROMOTIONAL
            },
            
            'direct_debit': {
                'account_number': '06012040022',  # Required string
                'amount': 100000.00,  # Required numeric min:0.01
                'mandate_reference': f'DD_TEST_{timestamp}',  # Required string
                'beneficiary_account': '06012040023',  # Required string
                'beneficiary_name': 'Test Beneficiary',  # Required string
                'frequency': 'monthly',  # Optional in:once,daily,weekly,monthly,yearly
                'start_date': datetime.now().strftime('%Y-%m-%d'),  # Optional date
                'end_date': (datetime.now() + timedelta(days=365)).strftime('%Y-%m-%d'),  # Optional date after:start_date
                'bank_code': 'NBC'  # Optional string
            },
            
            'control_number_payments': {
                'control_number': '991234567890',  # Required string
                'payer_name': 'Test Payer',  # Required string
                'payer_phone': '255700000000',  # Optional string
                'amount': 75000.00,  # Required numeric min:0.01
                'payment_description': 'Test control number payment',  # Optional string
                'currency': 'TZS',  # Optional string size:3
                'account_number': '06012040022'  # Required string
            },
            
            'luku': {
                'meter_number': '04123456789',  # Required string
                'amount': 10000.00,  # Required numeric min:1000
                'customer_phone': '255700000000',  # Required string
                'customer_name': 'Test LUKU Customer',  # Optional string
                'account_number': '28012040011'  # Optional string (defaults to 28012040011)
            },
            
            'gepg': {
                'bill_id': f'BILL_{timestamp}',  # Required string
                'payer_name': 'Test GEPG Payer',  # Required string
                'payer_phone': '255700000000',  # Required string
                'payer_email': 'test@example.com',  # Optional email
                'amount': 150000.00,  # Required numeric min:0.01
                'payment_option': 'exact',  # Required in:exact,partial,full
                'currency': 'TZS',  # Optional string size:3
                'account_number': '06012040022'  # Required string
            },
            
            'pay_by_link': {
                'amount': 25000.00,  # Required numeric min:0.01
                'description': 'Test payment link for debugging',  # Required string
                'customer_email': 'customer@test.com',  # Optional email
                'customer_phone': '255700000000',  # Optional string
                'customer_name': 'Test Link Customer',  # Optional string
                'expiry_hours': 24,  # Optional integer min:1 max:720
                'callback_url': 'https://webhook.site/test-callback',  # Optional url
                'currency': 'TZS'  # Optional string size:3
            }
        }
    
    def _load_expected_responses(self) -> Dict:
        """
        Load expected response structures based on actual controller responses
        """
        return {
            'internal_funds_transfer': {
                'status': 'success|error',
                'request_id': 'IFT_UUID',
                'message': 'Transfer processed',
                'data': {
                    'reference': 'TEST_INT_XXX',
                    'status': 'completed|pending|failed',
                    # Additional fields from service response
                },
                'transaction_id': 'TEST_INT_XXX',
                'timestamp': 'ISO8601',
                'processing_time_ms': 'float'
            },
            
            'external_funds_transfer': {
                'status': 'success|error',
                'request_id': 'EFT_UUID',
                'message': 'External transfer processed',
                'data': {
                    'reference': 'TEST_EXT_XXX',
                    'status': 'completed|pending|failed',
                    'routing_system': 'TIPS|TISS'
                },
                'transaction_id': 'TEST_EXT_XXX',
                'timestamp': 'ISO8601',
                'processing_time_ms': 'float'
            },
            
            'mini_statement': {
                'status': 'success|error',
                'request_id': 'MINI_UUID',
                'account_number': 'string',
                'account_name': 'string',
                'currency': 'TZS',
                'current_balance': 'float',
                'available_balance': 'float',
                'transactions': 'array',
                'generated_at': 'ISO8601',
                'processing_time_ms': 'float'
            },
            
            'full_statement': {
                'status': 'success|error',
                'request_id': 'FULL_UUID',
                'account_number': 'string',
                'account_name': 'string',
                'account_type': 'string',
                'currency': 'TZS',
                'period': {
                    'from': 'date',
                    'to': 'date'
                },
                'opening_balance': 'float',
                'closing_balance': 'float',
                'total_debits': 'float',
                'total_credits': 'float',
                'transaction_count': 'integer',
                'transactions': 'array',
                'format': 'json|pdf|excel',
                'generated_at': 'ISO8601',
                'processing_time_ms': 'float'
            },
            
            'account_lookup': {
                'status': 'success|error',
                'request_id': 'LOOKUP_UUID',
                'account': {
                    'accountNumber': 'string',
                    'accountName': 'string',
                    'accountType': 'string',
                    'status': 'Active|Inactive',
                    'currency': 'TZS'
                },
                'timestamp': 'ISO8601',
                'processing_time_ms': 'float'
            },
            
            'utilities': {
                'status': 'success|error',
                'request_id': 'UTIL_UUID',
                'reference_number': 'UTIL_XXX',
                'utility_type': 'string',
                'account_number': 'string',
                'amount': 'float',
                'payment_status': 'string',
                'timestamp': 'ISO8601',
                'message': 'string',
                'data': 'object|null',
                'processing_time_ms': 'float'
            },
            
            'sms': {
                'status': 'success|partial|error',
                'request_id': 'SMS_UUID',
                'message_id': 'SMS_XXX',
                'recipients': 'integer',
                'successful': 'integer',
                'failed': 'integer',
                'results': 'array',
                'errors': 'array',
                'message': 'string',
                'sender_id': 'string',
                'timestamp': 'ISO8601'
            },
            
            'direct_debit': {
                'status': 'success|error',
                'request_id': 'DD_UUID',
                'mandate_id': 'DD_XXX',
                'mandate_reference': 'string',
                'account_number': 'string',
                'beneficiary_account': 'string',
                'beneficiary_name': 'string',
                'amount': 'float',
                'frequency': 'string',
                'status': 'Active|Pending',
                'timestamp': 'ISO8601',
                'message': 'string',
                'data': 'object|null',
                'processing_time_ms': 'float'
            },
            
            'control_number_payments': {
                'status': 'success|error',
                'request_id': 'CN_UUID',
                'payment_reference': 'CN_PAY_XXX',
                'control_number': 'string',
                'payer_name': 'string',
                'amount': 'float',
                'currency': 'TZS',
                'payment_status': 'Processing|Completed|Failed',
                'receipt_number': 'string|null',
                'timestamp': 'ISO8601',
                'message': 'string',
                'data': 'object',
                'processing_time_ms': 'float'
            },
            
            'luku': {
                'status': 'success|error',
                'request_id': 'LUKU_UUID',
                'transaction_id': 'LUKU_XXX',
                'meter_number': 'string',
                'customer_phone': 'string',
                'customer_name': 'string',
                'amount': 'float',
                'token': 'string|null',
                'units': 'float|null',
                'timestamp': 'ISO8601',
                'message': 'string',
                'data': 'object',
                'processing_time_ms': 'float'
            },
            
            'gepg': {
                'status': 'success|error',
                'request_id': 'GEPG_UUID',
                'gepg_reference': 'GEPG_XXX',
                'control_number': 'string|null',
                'bill_id': 'string',
                'payer_name': 'string',
                'payer_phone': 'string',
                'amount': 'float',
                'currency': 'TZS',
                'payment_option': 'string',
                'expire_date': 'date',
                'payment_status': 'Pending',
                'timestamp': 'ISO8601',
                'message': 'string',
                'data': 'object',
                'processing_time_ms': 'float'
            },
            
            'pay_by_link': {
                'status': 'success|error',
                'request_id': 'LINK_UUID',
                'link_id': 'LINK_XXX',
                'payment_link': 'url|null',
                'short_link': 'url|null',
                'amount': 'float',
                'currency': 'TZS',
                'description': 'string',
                'customer_email': 'string|null',
                'customer_phone': 'string|null',
                'customer_name': 'string|null',
                'expiry_date': 'ISO8601',
                'callback_url': 'url|null',
                'status': 'Active',
                'qr_code': 'string|null',
                'timestamp': 'ISO8601',
                'message': 'string',
                'data': 'object|null',
                'processing_time_ms': 'float'
            }
        }
    
    def print_header(self, text: str, color=Fore.CYAN):
        width = 80
        print(f"\n{color}{'=' * width}")
        print(f"{color}{text.center(width)}")
        print(f"{color}{'=' * width}{Style.RESET_ALL}")
    
    def print_service_header(self, service_name: str):
        print(f"\n{Fore.YELLOW}{'─' * 80}")
        print(f"{Fore.YELLOW}Testing: {service_name}")
        print(f"{Fore.YELLOW}{'─' * 80}{Style.RESET_ALL}")
    
    def validate_response_structure(self, response_data: Dict, expected: Dict, service_name: str):
        """
        Validate that response matches expected structure
        """
        print(f"\n{Fore.CYAN}Response Validation:{Style.RESET_ALL}")
        validation_errors = []
        
        # Check for required fields in response
        if isinstance(expected, dict):
            for key, expected_type in expected.items():
                if key not in response_data:
                    validation_errors.append(f"Missing field: {key}")
                    print(f"  {Fore.RED}✗ Missing: {key}{Style.RESET_ALL}")
                else:
                    # Basic type checking
                    actual_value = response_data[key]
                    if expected_type == 'string' and not isinstance(actual_value, str):
                        validation_errors.append(f"Type mismatch: {key} should be string")
                    elif expected_type == 'integer' and not isinstance(actual_value, int):
                        validation_errors.append(f"Type mismatch: {key} should be integer")
                    elif expected_type == 'float' and not isinstance(actual_value, (int, float)):
                        validation_errors.append(f"Type mismatch: {key} should be numeric")
                    elif expected_type == 'array' and not isinstance(actual_value, list):
                        validation_errors.append(f"Type mismatch: {key} should be array")
                    elif expected_type == 'object' and not isinstance(actual_value, dict):
                        validation_errors.append(f"Type mismatch: {key} should be object")
                    else:
                        print(f"  {Fore.GREEN}✓ Valid: {key}{Style.RESET_ALL}")
        
        if validation_errors:
            print(f"\n{Fore.RED}Validation Errors:{Style.RESET_ALL}")
            for error in validation_errors:
                print(f"  • {error}")
        else:
            print(f"\n{Fore.GREEN}✓ Response structure validated successfully{Style.RESET_ALL}")
        
        return validation_errors
    
    def test_service(self, service_name: str, endpoint: str, test_data: Dict, expected_response: Dict) -> TestResult:
        """
        Test a single service with real data structures
        """
        self.print_service_header(service_name)
        
        request_id = f"TEST_{int(time.time() * 1000)}"
        headers = {'X-Request-ID': request_id}
        
        # Print request details
        print(f"{Fore.CYAN}Endpoint: {Style.RESET_ALL}{endpoint}")
        print(f"{Fore.CYAN}Method: {Style.RESET_ALL}POST")
        print(f"{Fore.CYAN}Request Data:{Style.RESET_ALL}")
        print(json.dumps(test_data, indent=2))
        
        try:
            # Make request
            start_time = time.time()
            response = self.session.post(
                f"{self.base_url}{endpoint}",
                json=test_data,
                headers=headers,
                timeout=60
            )
            duration_ms = (time.time() - start_time) * 1000
            
            # Parse response
            try:
                response_data = response.json()
            except json.JSONDecodeError:
                response_data = {'raw_response': response.text}
            
            # Determine status
            if response.status_code >= 200 and response.status_code < 300:
                status = TestStatus.SUCCESS
                print(f"\n{Fore.GREEN}✓ SUCCESS - HTTP {response.status_code} - {duration_ms:.2f}ms{Style.RESET_ALL}")
            else:
                status = TestStatus.FAILED
                print(f"\n{Fore.RED}✗ FAILED - HTTP {response.status_code} - {duration_ms:.2f}ms{Style.RESET_ALL}")
            
            # Print key response fields
            print(f"\n{Fore.CYAN}Response Summary:{Style.RESET_ALL}")
            if response_data.get('status'):
                color = Fore.GREEN if response_data['status'] == 'success' else Fore.RED
                print(f"  Status: {color}{response_data['status']}{Style.RESET_ALL}")
            
            if response_data.get('request_id'):
                print(f"  Request ID: {Fore.BLUE}{response_data['request_id']}{Style.RESET_ALL}")
            
            if response_data.get('message'):
                print(f"  Message: {response_data['message']}")
            
            if response_data.get('transaction_id'):
                print(f"  Transaction ID: {Fore.MAGENTA}{response_data['transaction_id']}{Style.RESET_ALL}")
            
            if response_data.get('processing_time_ms'):
                print(f"  Processing Time: {response_data['processing_time_ms']}ms")
            
            # Validate response structure
            validation_errors = self.validate_response_structure(response_data, expected_response, service_name)
            
            # Print full response in verbose mode
            if args.verbose:
                print(f"\n{Fore.CYAN}Full Response:{Style.RESET_ALL}")
                print(json.dumps(response_data, indent=2))
            
            # Print expected vs actual comparison
            if args.verbose and validation_errors:
                print(f"\n{Fore.YELLOW}Expected Structure:{Style.RESET_ALL}")
                print(json.dumps(expected_response, indent=2))
            
            # Create result
            result = TestResult(
                service_name=service_name,
                endpoint=endpoint,
                status=status,
                http_code=response.status_code,
                duration_ms=duration_ms,
                request_id=response_data.get('request_id'),
                request_data=test_data,
                response_data=response_data,
                expected_response=expected_response,
                error_message=response_data.get('message') if status == TestStatus.FAILED else None,
                timestamp=datetime.now().isoformat()
            )
            
        except requests.exceptions.Timeout:
            status = TestStatus.TIMEOUT
            print(f"\n{Fore.RED}✗ TIMEOUT - Request timed out after 60s{Style.RESET_ALL}")
            result = TestResult(
                service_name=service_name,
                endpoint=endpoint,
                status=status,
                http_code=0,
                duration_ms=60000,
                request_id=None,
                request_data=test_data,
                response_data=None,
                expected_response=expected_response,
                error_message="Request timed out after 60 seconds",
                timestamp=datetime.now().isoformat()
            )
            
        except Exception as e:
            status = TestStatus.ERROR
            print(f"\n{Fore.RED}✗ ERROR - {str(e)}{Style.RESET_ALL}")
            if args.verbose:
                import traceback
                print(f"{Fore.RED}Traceback:{Style.RESET_ALL}")
                print(traceback.format_exc())
            
            result = TestResult(
                service_name=service_name,
                endpoint=endpoint,
                status=status,
                http_code=0,
                duration_ms=0,
                request_id=None,
                request_data=test_data,
                response_data=None,
                expected_response=expected_response,
                error_message=str(e),
                timestamp=datetime.now().isoformat()
            )
        
        self.results.append(result)
        time.sleep(1)  # Delay between tests
        return result
    
    def run_all_tests(self):
        """
        Run tests for all services with real data
        """
        services = {
            'Internal Funds Transfer': ('/api/test-services/internal-funds-transfer', 'internal_funds_transfer'),
            'External Funds Transfer': ('/api/test-services/external-funds-transfer', 'external_funds_transfer'),
            'Mini Statement': ('/api/test-services/mini-statement', 'mini_statement'),
            'Full Statement': ('/api/test-services/full-statement', 'full_statement'),
            'Account Lookup': ('/api/test-services/account-lookup', 'account_lookup'),
            'Utilities': ('/api/test-services/utilities', 'utilities'),
            'SMS': ('/api/test-services/sms', 'sms'),
            'Direct Debit': ('/api/test-services/direct-debit', 'direct_debit'),
            'Control Number Payments': ('/api/test-services/control-number-payments', 'control_number_payments'),
            'LUKU': ('/api/test-services/luku', 'luku'),
            'GEPG': ('/api/test-services/gepg', 'gepg'),
            'Pay By Link': ('/api/test-services/pay-by-link', 'pay_by_link')
        }
        
        for service_name, (endpoint, data_key) in services.items():
            test_data = self.test_data[data_key]
            expected_response = self.expected_responses[data_key]
            self.test_service(service_name, endpoint, test_data, expected_response)
    
    def generate_detailed_report(self):
        """
        Generate comprehensive test report with validation results
        """
        self.print_header("COMPREHENSIVE TEST REPORT")
        
        # Summary statistics
        total = len(self.results)
        successful = sum(1 for r in self.results if r.status == TestStatus.SUCCESS)
        failed = sum(1 for r in self.results if r.status == TestStatus.FAILED)
        errors = sum(1 for r in self.results if r.status == TestStatus.ERROR)
        timeouts = sum(1 for r in self.results if r.status == TestStatus.TIMEOUT)
        
        # Create detailed summary table
        summary_data = []
        for result in self.results:
            status_symbol = {
                TestStatus.SUCCESS: f"{Fore.GREEN}✓ PASS{Style.RESET_ALL}",
                TestStatus.FAILED: f"{Fore.RED}✗ FAIL{Style.RESET_ALL}",
                TestStatus.ERROR: f"{Fore.RED}✗ ERROR{Style.RESET_ALL}",
                TestStatus.TIMEOUT: f"{Fore.YELLOW}⏱ TIMEOUT{Style.RESET_ALL}",
            }.get(result.status, "?")
            
            summary_data.append([
                result.service_name[:25],
                status_symbol,
                f"{result.http_code}" if result.http_code else "N/A",
                f"{result.duration_ms:.0f}ms" if result.duration_ms else "N/A",
                result.request_id[:15] + "..." if result.request_id and len(result.request_id) > 15 else result.request_id or "N/A",
                result.error_message[:30] + "..." if result.error_message and len(result.error_message) > 30 else result.error_message or "OK"
            ])
        
        # Print detailed table
        headers = ["Service", "Status", "HTTP", "Time", "Request ID", "Message"]
        print(tabulate(summary_data, headers=headers, tablefmt="grid"))
        
        # Print statistics
        print(f"\n{Fore.CYAN}Test Statistics:{Style.RESET_ALL}")
        print(f"  Total Tests: {total}")
        print(f"  {Fore.GREEN}Successful: {successful} ({successful/total*100:.1f}%){Style.RESET_ALL}")
        print(f"  {Fore.RED}Failed: {failed} ({failed/total*100:.1f}%){Style.RESET_ALL}")
        print(f"  {Fore.RED}Errors: {errors} ({errors/total*100:.1f}%){Style.RESET_ALL}")
        print(f"  {Fore.YELLOW}Timeouts: {timeouts} ({timeouts/total*100:.1f}%){Style.RESET_ALL}")
        
        # Calculate performance metrics
        valid_durations = [r.duration_ms for r in self.results if r.duration_ms > 0]
        if valid_durations:
            avg_duration = sum(valid_durations) / len(valid_durations)
            min_duration = min(valid_durations)
            max_duration = max(valid_durations)
            
            print(f"\n{Fore.CYAN}Performance Metrics:{Style.RESET_ALL}")
            print(f"  Average Response Time: {avg_duration:.2f}ms")
            print(f"  Fastest Response: {min_duration:.2f}ms")
            print(f"  Slowest Response: {max_duration:.2f}ms")
        
        # Success rate
        success_rate = (successful / total * 100) if total > 0 else 0
        color = Fore.GREEN if success_rate >= 80 else Fore.YELLOW if success_rate >= 50 else Fore.RED
        print(f"\n  {color}Overall Success Rate: {success_rate:.1f}%{Style.RESET_ALL}")
        
        # Save detailed results
        timestamp = datetime.now().strftime("%Y-%m-%d_%H-%M-%S")
        json_file = f"test_results/real_test_results_{timestamp}.json"
        
        # Convert results to detailed dictionaries
        results_dict = []
        for result in self.results:
            result_dict = asdict(result)
            result_dict['status'] = result.status.value
            results_dict.append(result_dict)
        
        # Save comprehensive JSON
        with open(json_file, 'w') as f:
            json.dump({
                'metadata': {
                    'environment': self.environment,
                    'base_url': self.base_url,
                    'timestamp': timestamp,
                    'test_duration': datetime.now().isoformat(),
                    'statistics': {
                        'total_tests': total,
                        'successful': successful,
                        'failed': failed,
                        'errors': errors,
                        'timeouts': timeouts,
                        'success_rate': success_rate
                    },
                    'performance': {
                        'average_response_ms': avg_duration if valid_durations else 0,
                        'min_response_ms': min_duration if valid_durations else 0,
                        'max_response_ms': max_duration if valid_durations else 0
                    }
                },
                'test_configuration': {
                    'api_key': self.api_key[:10] + '...',
                    'timeout': 60,
                    'delay_between_tests': 1
                },
                'results': results_dict
            }, f, indent=2)
        
        print(f"\n{Fore.CYAN}Detailed results saved to: {json_file}{Style.RESET_ALL}")
        
        # Print detailed failure analysis
        if failed > 0 or errors > 0:
            print(f"\n{Fore.RED}═══ FAILURE ANALYSIS ═══{Style.RESET_ALL}")
            for result in self.results:
                if result.status in [TestStatus.FAILED, TestStatus.ERROR, TestStatus.TIMEOUT]:
                    print(f"\n{Fore.YELLOW}► {result.service_name}{Style.RESET_ALL}")
                    print(f"  Status: {Fore.RED}{result.status.value}{Style.RESET_ALL}")
                    print(f"  HTTP Code: {result.http_code}")
                    print(f"  Error: {result.error_message}")
                    
                    if result.response_data and args.verbose:
                        print(f"  Response Details:")
                        if isinstance(result.response_data, dict):
                            for key, value in result.response_data.items():
                                if key in ['message', 'error', 'details']:
                                    print(f"    {key}: {value}")

def main():
    global args
    
    parser = argparse.ArgumentParser(
        description='API Service Test Suite with Real Request/Response Structures',
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""
Examples:
  %(prog)s                     # Test all services
  %(prog)s --verbose           # Test with detailed output
  %(prog)s --service luku      # Test specific service
  %(prog)s --env staging       # Test on staging environment
  %(prog)s --list              # List available services
        """
    )
    
    parser.add_argument('--env', default='local', 
                        choices=['local', 'staging', 'production'],
                        help='Environment to test (default: local)')
    parser.add_argument('--url', help='Override base URL')
    parser.add_argument('-v', '--verbose', action='store_true', 
                        help='Enable verbose output with full responses')
    parser.add_argument('--service', help='Test specific service')
    parser.add_argument('--list', action='store_true', 
                        help='List all available services')
    
    args = parser.parse_args()
    
    # Determine base URL
    if args.url:
        base_url = args.url
    else:
        urls = {
            'local': 'http://127.0.0.1:8000',
            'staging': 'https://staging.example.com',
            'production': 'https://api.example.com'
        }
        base_url = urls.get(args.env, 'http://127.0.0.1:8000')
    
    # Print banner
    print(f"{Fore.CYAN}╔═══════════════════════════════════════════════════════════════════════════╗")
    print(f"║          REAL API SERVICE TEST SUITE WITH ACTUAL STRUCTURES              ║")
    print(f"╚═══════════════════════════════════════════════════════════════════════════╝{Style.RESET_ALL}")
    
    print(f"\n{Fore.BLUE}Configuration:{Style.RESET_ALL}")
    print(f"  Environment: {args.env}")
    print(f"  Base URL: {base_url}")
    print(f"  Verbose Mode: {args.verbose}")
    print(f"  Test Type: {'Single Service' if args.service else 'All Services'}")
    
    # Create tester
    tester = RealServiceTester(base_url, args.env)
    
    # Run tests
    if args.list:
        print(f"\n{Fore.CYAN}Available Services:{Style.RESET_ALL}")
        services = [
            ('internal-funds-transfer', 'Internal funds transfer between NBC accounts'),
            ('external-funds-transfer', 'External funds transfer via TIPS/TISS'),
            ('mini-statement', 'Get last 5-10 transactions'),
            ('full-statement', 'Get full statement for date range'),
            ('account-lookup', 'Lookup account details'),
            ('utilities', 'Pay utility bills'),
            ('sms', 'Send SMS messages'),
            ('direct-debit', 'Setup direct debit mandates'),
            ('control-number-payments', 'Process GEPG control number payments'),
            ('luku', 'Purchase LUKU electricity tokens'),
            ('gepg', 'Generate GEPG control numbers'),
            ('pay-by-link', 'Generate payment links')
        ]
        
        for service_id, description in services:
            print(f"  • {Fore.GREEN}{service_id:<25}{Style.RESET_ALL} {description}")
            
    elif args.service:
        # Test single service
        service_map = {
            'internal-funds-transfer': ('Internal Funds Transfer', '/api/test-services/internal-funds-transfer', 'internal_funds_transfer'),
            'external-funds-transfer': ('External Funds Transfer', '/api/test-services/external-funds-transfer', 'external_funds_transfer'),
            'mini-statement': ('Mini Statement', '/api/test-services/mini-statement', 'mini_statement'),
            'full-statement': ('Full Statement', '/api/test-services/full-statement', 'full_statement'),
            'account-lookup': ('Account Lookup', '/api/test-services/account-lookup', 'account_lookup'),
            'utilities': ('Utilities', '/api/test-services/utilities', 'utilities'),
            'sms': ('SMS', '/api/test-services/sms', 'sms'),
            'direct-debit': ('Direct Debit', '/api/test-services/direct-debit', 'direct_debit'),
            'control-number-payments': ('Control Number Payments', '/api/test-services/control-number-payments', 'control_number_payments'),
            'luku': ('LUKU', '/api/test-services/luku', 'luku'),
            'gepg': ('GEPG', '/api/test-services/gepg', 'gepg'),
            'pay-by-link': ('Pay By Link', '/api/test-services/pay-by-link', 'pay_by_link')
        }
        
        if args.service in service_map:
            service_name, endpoint, data_key = service_map[args.service]
            test_data = tester.test_data[data_key]
            expected_response = tester.expected_responses[data_key]
            tester.test_service(service_name, endpoint, test_data, expected_response)
            tester.generate_detailed_report()
        else:
            print(f"{Fore.RED}Unknown service: {args.service}{Style.RESET_ALL}")
            print("Use --list to see available services")
            
    else:
        # Test all services
        print(f"\n{Fore.YELLOW}Starting comprehensive test suite...{Style.RESET_ALL}")
        tester.run_all_tests()
        tester.generate_detailed_report()

if __name__ == "__main__":
    main()