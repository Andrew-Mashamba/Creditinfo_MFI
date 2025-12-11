#!/usr/bin/env python3
"""
Comprehensive API Service Test Suite
Tests all endpoints with detailed logging and error reporting
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

# Initialize colorama for cross-platform colored output
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
    error_message: Optional[str]
    timestamp: str

class ServiceTester:
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
            'User-Agent': 'ServiceTestSuite/2.0'
        })
        
        # Create results directory
        os.makedirs('test_results', exist_ok=True)
        
        # Test data
        self.test_data = self._load_test_data()
        
    def _get_api_key(self, environment: str) -> str:
        """Get API key based on environment"""
        keys = {
            'local': 'local_test_api_key',
            'staging': 'staging_api_key',
            'production': 'production_api_key'
        }
        return keys.get(environment, 'test_api_key')
    
    def _load_test_data(self) -> Dict:
        """Load test data for each service"""
        timestamp = int(time.time())
        return {
            'internal_funds_transfer': {
                'source_account': '06012040022',
                'destination_account': '06012040023',
                'amount': 10000.00,
                'currency': 'TZS',
                'description': f'Test internal transfer at {datetime.now()}',
                'reference': f'TEST_INT_{timestamp}',
                'beneficiary_name': 'John Doe Test'
            },
            'external_funds_transfer': {
                'source_account': '06012040022',
                'destination_account': '1234567890',
                'destination_bank': 'CRDB',
                'swift_code': 'CORUTZTZ',
                'amount': 50000.00,
                'currency': 'TZS',
                'beneficiary_name': 'Jane Smith External',
                'description': f'Test external transfer at {datetime.now()}',
                'reference': f'TEST_EXT_{timestamp}'
            },
            'mini_statement': {
                'account_number': '06012040022',
                'limit': 5,
                'channel_code': 'SACCOSNBC'
            },
            'full_statement': {
                'account_number': '06012040022',
                'from_date': (datetime.now() - timedelta(days=30)).strftime('%Y-%m-%d'),
                'to_date': datetime.now().strftime('%Y-%m-%d'),
                'format': 'json',
                'channel_code': 'SACCOSNBC'
            },
            'account_lookup': {
                'account_number': '06012040022',
                'channel_code': 'SACCOSNBC'
            },
            'utilities': {
                'utility_type': 'electricity',
                'account_number': '06012040022',
                'meter_number': 'LUKU123456',
                'amount': 20000.00,
                'customer_name': 'Test Customer',
                'phone_number': '255700000000',
                'provider_code': 'TANESCO'
            },
            'sms': {
                'phone_numbers': ['255700000001', '255700000002'],
                'message': f'Test SMS from API test at {datetime.now().strftime("%H:%M:%S")}',
                'sender_id': 'SACCOS',
                'sms_type': 'TRANSACTIONAL'
            },
            'direct_debit': {
                'account_number': '06012040022',
                'amount': 100000.00,
                'mandate_reference': f'DD_TEST_{timestamp}',
                'beneficiary_account': '06012040023',
                'beneficiary_name': 'Test Beneficiary',
                'frequency': 'monthly',
                'start_date': datetime.now().strftime('%Y-%m-%d'),
                'end_date': (datetime.now() + timedelta(days=365)).strftime('%Y-%m-%d'),
                'bank_code': 'NBC'
            },
            'control_number_payments': {
                'control_number': '991234567890',
                'payer_name': 'Test Payer',
                'payer_phone': '255700000000',
                'amount': 75000.00,
                'payment_description': 'Test control number payment',
                'currency': 'TZS',
                'account_number': '06012040022'
            },
            'luku': {
                'meter_number': '04123456789',
                'amount': 10000.00,
                'customer_phone': '255700000000',
                'customer_name': 'Test LUKU Customer',
                'account_number': '28012040011'
            },
            'gepg': {
                'bill_id': f'BILL_{timestamp}',
                'payer_name': 'Test GEPG Payer',
                'payer_phone': '255700000000',
                'payer_email': 'test@example.com',
                'amount': 150000.00,
                'payment_option': 'exact',
                'currency': 'TZS',
                'account_number': '06012040022'
            },
            'pay_by_link': {
                'amount': 25000.00,
                'description': 'Test payment link for debugging',
                'customer_email': 'customer@test.com',
                'customer_phone': '255700000000',
                'customer_name': 'Test Link Customer',
                'expiry_hours': 24,
                'callback_url': 'https://webhook.site/test-callback',
                'currency': 'TZS'
            }
        }
    
    def print_header(self, text: str, color=Fore.CYAN):
        """Print formatted header"""
        width = 80
        print(f"\n{color}{'=' * width}")
        print(f"{color}{text.center(width)}")
        print(f"{color}{'=' * width}{Style.RESET_ALL}")
    
    def print_service_header(self, service_name: str):
        """Print service test header"""
        print(f"\n{Fore.YELLOW}{'─' * 80}")
        print(f"{Fore.YELLOW}Testing: {service_name}")
        print(f"{Fore.YELLOW}{'─' * 80}{Style.RESET_ALL}")
    
    def test_service(self, service_name: str, endpoint: str, test_data: Dict) -> TestResult:
        """Test a single service endpoint"""
        self.print_service_header(service_name)
        
        # Add request ID
        request_id = f"TEST_{int(time.time() * 1000)}"
        headers = {'X-Request-ID': request_id}
        
        # Print request details
        print(f"{Fore.CYAN}Endpoint: {Style.RESET_ALL}{endpoint}")
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
                print(f"{Fore.GREEN}✓ SUCCESS - HTTP {response.status_code} - {duration_ms:.2f}ms{Style.RESET_ALL}")
            else:
                status = TestStatus.FAILED
                print(f"{Fore.RED}✗ FAILED - HTTP {response.status_code} - {duration_ms:.2f}ms{Style.RESET_ALL}")
            
            # Print response details
            if response_data.get('request_id'):
                print(f"{Fore.BLUE}Request ID: {response_data['request_id']}{Style.RESET_ALL}")
            
            if response_data.get('message'):
                color = Fore.GREEN if status == TestStatus.SUCCESS else Fore.RED
                print(f"{color}Message: {response_data['message']}{Style.RESET_ALL}")
            
            # Print full response in debug mode
            if args.verbose:
                print(f"\n{Fore.CYAN}Full Response:{Style.RESET_ALL}")
                print(json.dumps(response_data, indent=2))
            
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
                error_message=response_data.get('message') if status == TestStatus.FAILED else None,
                timestamp=datetime.now().isoformat()
            )
            
        except requests.exceptions.Timeout:
            status = TestStatus.TIMEOUT
            print(f"{Fore.RED}✗ TIMEOUT - Request timed out{Style.RESET_ALL}")
            result = TestResult(
                service_name=service_name,
                endpoint=endpoint,
                status=status,
                http_code=0,
                duration_ms=60000,
                request_id=None,
                request_data=test_data,
                response_data=None,
                error_message="Request timed out",
                timestamp=datetime.now().isoformat()
            )
            
        except Exception as e:
            status = TestStatus.ERROR
            print(f"{Fore.RED}✗ ERROR - {str(e)}{Style.RESET_ALL}")
            result = TestResult(
                service_name=service_name,
                endpoint=endpoint,
                status=status,
                http_code=0,
                duration_ms=0,
                request_id=None,
                request_data=test_data,
                response_data=None,
                error_message=str(e),
                timestamp=datetime.now().isoformat()
            )
        
        self.results.append(result)
        time.sleep(1)  # Delay between tests
        return result
    
    def run_all_tests(self):
        """Run tests for all services"""
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
            self.test_service(service_name, endpoint, test_data)
    
    def generate_report(self):
        """Generate test report"""
        self.print_header("TEST SUMMARY REPORT")
        
        # Summary statistics
        total = len(self.results)
        successful = sum(1 for r in self.results if r.status == TestStatus.SUCCESS)
        failed = sum(1 for r in self.results if r.status == TestStatus.FAILED)
        errors = sum(1 for r in self.results if r.status == TestStatus.ERROR)
        timeouts = sum(1 for r in self.results if r.status == TestStatus.TIMEOUT)
        
        # Create summary table
        summary_data = []
        for result in self.results:
            status_symbol = {
                TestStatus.SUCCESS: f"{Fore.GREEN}✓{Style.RESET_ALL}",
                TestStatus.FAILED: f"{Fore.RED}✗{Style.RESET_ALL}",
                TestStatus.ERROR: f"{Fore.RED}✗{Style.RESET_ALL}",
                TestStatus.TIMEOUT: f"{Fore.YELLOW}⏱{Style.RESET_ALL}",
            }.get(result.status, "?")
            
            summary_data.append([
                result.service_name,
                status_symbol,
                result.http_code if result.http_code else "N/A",
                f"{result.duration_ms:.2f}ms" if result.duration_ms else "N/A",
                result.request_id[:20] + "..." if result.request_id and len(result.request_id) > 20 else result.request_id or "N/A"
            ])
        
        # Print table
        headers = ["Service", "Status", "HTTP", "Duration", "Request ID"]
        print(tabulate(summary_data, headers=headers, tablefmt="grid"))
        
        # Print statistics
        print(f"\n{Fore.CYAN}Statistics:{Style.RESET_ALL}")
        print(f"  Total Tests: {total}")
        print(f"  {Fore.GREEN}Successful: {successful}{Style.RESET_ALL}")
        print(f"  {Fore.RED}Failed: {failed}{Style.RESET_ALL}")
        print(f"  {Fore.RED}Errors: {errors}{Style.RESET_ALL}")
        print(f"  {Fore.YELLOW}Timeouts: {timeouts}{Style.RESET_ALL}")
        
        # Calculate average response time
        valid_durations = [r.duration_ms for r in self.results if r.duration_ms > 0]
        if valid_durations:
            avg_duration = sum(valid_durations) / len(valid_durations)
            print(f"  Average Response Time: {avg_duration:.2f}ms")
        
        # Success rate
        success_rate = (successful / total * 100) if total > 0 else 0
        color = Fore.GREEN if success_rate >= 80 else Fore.YELLOW if success_rate >= 50 else Fore.RED
        print(f"  {color}Success Rate: {success_rate:.1f}%{Style.RESET_ALL}")
        
        # Save results to file
        timestamp = datetime.now().strftime("%Y-%m-%d_%H-%M-%S")
        json_file = f"test_results/test_results_{timestamp}.json"
        
        # Convert results to dictionaries
        results_dict = []
        for result in self.results:
            result_dict = asdict(result)
            result_dict['status'] = result.status.value
            results_dict.append(result_dict)
        
        # Save JSON
        with open(json_file, 'w') as f:
            json.dump({
                'metadata': {
                    'environment': self.environment,
                    'base_url': self.base_url,
                    'timestamp': timestamp,
                    'total_tests': total,
                    'successful': successful,
                    'failed': failed,
                    'errors': errors,
                    'timeouts': timeouts,
                    'success_rate': success_rate
                },
                'results': results_dict
            }, f, indent=2)
        
        print(f"\n{Fore.CYAN}Results saved to: {json_file}{Style.RESET_ALL}")
        
        # Print failed test details
        if failed > 0 or errors > 0:
            print(f"\n{Fore.RED}Failed Test Details:{Style.RESET_ALL}")
            for result in self.results:
                if result.status in [TestStatus.FAILED, TestStatus.ERROR]:
                    print(f"\n  {result.service_name}:")
                    print(f"    Status: {result.status.value}")
                    print(f"    Error: {result.error_message}")
                    if result.response_data and args.verbose:
                        print(f"    Response: {json.dumps(result.response_data, indent=6)}")

def main():
    global args
    
    parser = argparse.ArgumentParser(description='API Service Test Suite')
    parser.add_argument('--env', default='local', choices=['local', 'staging', 'production'],
                        help='Environment to test')
    parser.add_argument('--url', help='Override base URL')
    parser.add_argument('-v', '--verbose', action='store_true', help='Verbose output')
    parser.add_argument('--service', help='Test specific service')
    parser.add_argument('--list', action='store_true', help='List available services')
    
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
    print(f"{Fore.CYAN}╔═══════════════════════════════════════════════════════════════════════╗")
    print(f"║                     API SERVICE COMPREHENSIVE TEST SUITE              ║")
    print(f"╚═══════════════════════════════════════════════════════════════════════╝{Style.RESET_ALL}")
    
    print(f"\nEnvironment: {args.env}")
    print(f"Base URL: {base_url}")
    print(f"Verbose: {args.verbose}")
    
    # Create tester
    tester = ServiceTester(base_url, args.env)
    
    # Run tests
    if args.list:
        services = [
            'internal-funds-transfer', 'external-funds-transfer', 'mini-statement',
            'full-statement', 'account-lookup', 'utilities', 'sms', 'direct-debit',
            'control-number-payments', 'luku', 'gepg', 'pay-by-link'
        ]
        print("\nAvailable services:")
        for service in services:
            print(f"  • {service}")
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
            tester.test_service(service_name, endpoint, test_data)
            tester.generate_report()
        else:
            print(f"{Fore.RED}Unknown service: {args.service}{Style.RESET_ALL}")
            print("Use --list to see available services")
    else:
        # Test all services
        tester.run_all_tests()
        tester.generate_report()

if __name__ == "__main__":
    main()