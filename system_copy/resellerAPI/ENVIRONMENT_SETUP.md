# Environment Setup for Domain Management

## Required Environment Variables

Add these variables to your main `.env` file:

```env
# Reseller API Configuration
RESELLER_API_URL=https://reseller.co.tz/api
RESELLER_API_KEY=your_api_key_here
RESELLER_TIMEOUT=30
```

## Configuration Details

### RESELLER_API_URL
- **Description**: Base URL for the Reseller API
- **Default**: `https://reseller.co.tz/api`
- **Required**: Yes

### RESELLER_API_KEY
- **Description**: Your API key from the reseller account
- **Format**: Usually starts with `sk_live_` or `sk_test_`
- **Required**: Yes
- **Security**: Keep this secure and never commit to version control

### RESELLER_TIMEOUT
- **Description**: Request timeout in seconds
- **Default**: `30`
- **Required**: No

## Getting Your API Key

1. Log in to your reseller account at `https://reseller.co.tz`
2. Navigate to API settings or developer section
3. Generate a new API key
4. Copy the key and add it to your `.env` file

## Testing the Configuration

After setting up the environment variables, you can test the API connection by:

1. Going to Profile Settings → Domain Management
2. Trying to check domain availability
3. If configured correctly, you should see API responses

## Security Notes

- Never commit your `.env` file to version control
- Use different API keys for development and production
- Regularly rotate your API keys
- Monitor API usage for any suspicious activity

## Troubleshooting

### Common Issues

1. **"API key is not configured" error**
   - Check that `RESELLER_API_KEY` is set in your `.env` file
   - Restart your application after adding the environment variable

2. **"Invalid API key" error**
   - Verify your API key is correct
   - Check if the API key has expired
   - Ensure you're using the correct environment (test vs live)

3. **Connection timeout errors**
   - Increase the `RESELLER_TIMEOUT` value
   - Check your internet connection
   - Verify the API URL is accessible

4. **Rate limit exceeded**
   - The API has a limit of 100 requests per minute
   - Implement proper caching to reduce API calls
   - Consider implementing retry logic with exponential backoff
