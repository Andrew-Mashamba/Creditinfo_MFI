# SACCOS Core System - Reports Management Guide

## Overview
The enhanced Reports Management system provides comprehensive financial reporting capabilities with professional analytics, automated scheduling, and regulatory compliance features.

## Features

### 🎯 **Analytics Dashboard**
- **Real-time KPIs**: Total members, loans, savings, deposits, shares
- **Performance Metrics**: Portfolio at Risk (PAR), Capital Adequacy Ratio, Liquidity Ratio
- **Financial Summary**: Total assets, liabilities, equity positions
- **Interactive Charts**: Visual representation of key metrics

### 📊 **Professional Reporting**
- **Regulatory Reports**: BOT and IFRS compliant financial statements
- **Operational Reports**: Daily operations and member management
- **Analytical Reports**: Performance indicators and trends
- **Multiple Formats**: PDF, Excel, CSV export options

### ⏰ **Automated Scheduling**
- **Scheduled Reports**: Set up recurring report generation
- **Email Delivery**: Automatic email delivery with attachments
- **Multiple Frequencies**: Daily, weekly, monthly, quarterly, annually
- **Recipient Management**: Multiple email recipients support

### 🔒 **Compliance & Security**
- **Regulatory Compliance**: BOT, IFRS, TCDC standards
- **Audit Trail**: Complete report generation history
- **Access Control**: Role-based permissions
- **Data Security**: Encrypted storage and transmission

## Getting Started

### 1. Accessing Reports Management
1. Navigate to **Reports Management** in the main menu
2. The dashboard will display real-time analytics
3. Use the sidebar to access different report categories

### 2. Generating Reports
1. **Select Report Type**: Choose from Regulatory or Operational reports
2. **Set Parameters**: Configure date ranges, filters, and options
3. **Generate Report**: Click generate to create the report
4. **Export Options**: Download as PDF, Excel, or CSV

### 3. Scheduling Reports
1. **Click "Schedule Report"** button
2. **Configure Settings**:
   - Report type and parameters
   - Frequency (once, daily, weekly, monthly)
   - Email recipients
   - Subject and message
3. **Set Schedule**: Choose date and time
4. **Save Schedule**: Report will be automatically generated and sent

## Report Categories

### Regulatory Reports
- **Statement of Financial Position**: Balance sheet with assets, liabilities & equity
- **Statement of Comprehensive Income**: Income statement with revenue & expenses
- **Statement of Cash Flow**: Cash flow analysis & liquidity position
- **Sectoral Classification of Loans**: Loan distribution by economic sector
- **Interest Rates Structure**: Loan interest rate analysis
- **Capital Adequacy**: Regulatory capital requirements
- **Compliance Reports**: BOT and TCDC compliance status

### Operational Reports
- **Members Details Report**: Comprehensive member information
- **Loan Portfolio Report**: Loan analysis & performance metrics
- **Financial Ratios**: Performance indicators & metrics
- **Savings Reports**: Deposit and withdrawal analysis
- **Share Reports**: Shareholding and dividend information

## Analytics Dashboard

### Key Performance Indicators
- **Total Members**: Active and inactive member counts
- **Total Loans**: Active loans and overdue amounts
- **Portfolio at Risk**: Percentage of loans at risk (30+ days overdue)
- **Capital Adequacy**: Financial strength ratio

### Financial Summary
- **Total Savings**: Member savings balances
- **Total Deposits**: Deposit account balances
- **Total Shares**: Share capital and equity

### Risk Assessment
- **Low Risk**: PAR < 2%
- **Medium Risk**: PAR 2-5%
- **High Risk**: PAR > 5%

## Automated Features

### Scheduled Reports
The system automatically generates and delivers reports based on configured schedules:

1. **Daily Reports**: Generated every day at specified time
2. **Weekly Reports**: Generated every Sunday at 6:00 AM
3. **Monthly Reports**: Generated on the 1st of each month at 7:00 AM
4. **Custom Schedules**: User-defined frequencies and times

### Email Delivery
- **Professional Templates**: Branded email with report summary
- **Multiple Formats**: PDF, Excel, CSV attachments
- **Delivery Tracking**: Confirmation and error logging
- **Retry Logic**: Automatic retry on delivery failure

### File Management
- **Automatic Cleanup**: Old files deleted after 30 days
- **Storage Optimization**: Efficient file organization
- **Backup Protection**: Secure file storage and backup

## Configuration

### Environment Variables
Add these to your `.env` file:

```env
# Report Email Configuration
REPORTS_FROM_ADDRESS=reports@saccos.com
REPORTS_FROM_NAME="SACCOS Reports System"
REPORTS_REPLY_TO=support@saccos.com
REPORTS_REPLY_TO_NAME="SACCOS Support"
REPORTS_SUBJECT_PREFIX="[SACCOS Reports]"
REPORTS_MAX_ATTACHMENT_SIZE=10485760
REPORTS_RETRY_ATTEMPTS=3
REPORTS_RETRY_DELAY=300
```

### Cron Job Setup
Add this to your server's crontab for automated execution:

```bash
# Laravel Scheduler - Run every minute
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## Troubleshooting

### Common Issues

#### Reports Not Generating
1. **Check Cron Job**: Ensure Laravel scheduler is running
2. **Verify Permissions**: Check file and directory permissions
3. **Review Logs**: Check `storage/logs/scheduled-reports.log`

#### Email Delivery Issues
1. **SMTP Configuration**: Verify email server settings
2. **Attachment Size**: Check file size limits
3. **Recipient Addresses**: Validate email addresses

#### Performance Issues
1. **Database Optimization**: Check query performance
2. **File Cleanup**: Run cleanup command manually
3. **Server Resources**: Monitor CPU and memory usage

### Manual Commands

#### Generate Scheduled Reports
```bash
php artisan reports:generate-scheduled
```

#### Clean Up Old Files
```bash
php artisan reports:cleanup-old-files --days=30
```

#### Force Generate All Reports
```bash
php artisan reports:generate-scheduled --force
```

## Best Practices

### Report Scheduling
1. **Avoid Peak Hours**: Schedule during off-peak times
2. **Test Schedules**: Verify email delivery before production
3. **Monitor Usage**: Track report generation and delivery
4. **Regular Review**: Update schedules based on business needs

### Data Management
1. **Regular Backups**: Ensure data is backed up regularly
2. **Archive Old Reports**: Move old reports to archive storage
3. **Monitor Storage**: Track disk space usage
4. **Clean Up**: Remove unnecessary files periodically

### Security
1. **Access Control**: Limit report access to authorized users
2. **Audit Logging**: Monitor report access and generation
3. **Data Encryption**: Ensure sensitive data is encrypted
4. **Regular Updates**: Keep system updated with security patches

## Support

### Getting Help
1. **Documentation**: Review this guide and system documentation
2. **Logs**: Check application logs for error details
3. **Support Team**: Contact system administrators for assistance
4. **Training**: Request training sessions for new features

### Contact Information
- **Technical Support**: support@saccos.com
- **System Administrator**: admin@saccos.com
- **Emergency Contact**: +255 XXX XXX XXX

## Version History

### v2.0.0 (Current)
- Enhanced analytics dashboard
- Automated report scheduling
- Professional email templates
- Regulatory compliance features
- Performance optimizations

### v1.0.0 (Previous)
- Basic report generation
- Manual export functionality
- Simple email delivery

---

**Last Updated**: August 29, 2025  
**System Version**: 2.0.0  
**Compatibility**: Laravel 10+, PHP 8.2+
