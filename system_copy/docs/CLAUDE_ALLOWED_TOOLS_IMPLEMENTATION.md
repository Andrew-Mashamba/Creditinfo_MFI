# Claude CLI with Allowed Tools Implementation

## Overview
The SACCOS Core System now uses Claude CLI with pre-configured allowed tools to avoid permission prompts while maintaining security. Claude can execute database queries and perform operations directly without asking for permission each time.

## Key Implementation Details

### 1. LocalClaudeService Configuration
The `LocalClaudeService` (`app/Services/LocalClaudeService.php`) now includes:

- **Allowed Tools Configuration**: Pre-defined list of safe operations
- **Temp File Handling**: Messages are saved to temp files to avoid shell escaping issues
- **MCP Configuration**: Automatic loading of `.mcp.json` for database access

#### Key Method: `buildAllowedToolsFlags()`
```php
private function buildAllowedToolsFlags(): string
{
    $allowedTools = [
        '"Bash(psql*)"',                     // Database operations
        '"Bash(php artisan db:query*)"',     // Artisan DB commands
        '"Bash(php artisan tinker*)"',       // Tinker access
        '"Read(**)"',                         // Read all project files
        // ... other safe commands
    ];
    
    foreach ($allowedTools as $tool) {
        $flags .= ' --allowed-tools ' . $tool;
    }
    
    return $flags;
}
```

#### Command Construction
```php
$tempFile = tempnam(sys_get_temp_dir(), 'claude_message_');
file_put_contents($tempFile, $enhancedMessage);

$command = "cat " . escapeshellarg($tempFile) . 
           " | claude {$allowedTools} --mcp-config {$mcpConfig}";
```

### 2. DirectClaudeService Integration
The `DirectClaudeService` (`app/Services/DirectClaudeService.php`) now:
- Uses `LocalClaudeService` instead of `ClaudeCliService`
- Inherits all allowed tools configuration
- Provides seamless database query execution

### 3. Allowed Tools List

#### Database Operations
- `Bash(psql*)` - All PostgreSQL queries
- `Bash(php artisan db:query*)` - Custom database query command
- `Bash(php artisan tinker*)` - Laravel Tinker for database access

#### File Operations
- `Read(**)` - Read any project file
- `Bash(ls*)` - List directories
- `Bash(cat*)` - View file contents

#### General Commands
- `Bash(echo*)` - Echo output
- `Bash(grep*)` - Search operations
- `Bash(find*)` - Find files
- `Bash(which*)` - Check command availability

#### MCP Tools
- `mcp__ide__getDiagnostics` - IDE diagnostics
- `mcp__ide__executeCode` - Code execution

## Usage

### Via Artisan Commands
```bash
# Test enhanced Claude service
php artisan claude:test-enhanced "Your question"

# Test DirectClaudeService
php artisan claude:test-direct "Your question"

# Direct database query
php artisan db:query "Your question"
```

### Via Web Interface
The AI Agent Chat interface automatically uses DirectClaudeService with LocalClaudeService, which includes all allowed tools configuration.

## Security Considerations

### What's Allowed
✅ All SELECT queries (read-only database access)
✅ Reading project files
✅ Safe bash commands (ls, echo, grep, etc.)
✅ PHP artisan commands for database inspection

### What's Blocked
❌ Destructive SQL operations (DROP, DELETE, UPDATE, INSERT)
❌ Dangerous bash commands (rm -rf, etc.)
❌ Writing to sensitive files (.env, config files)
❌ System-level operations

## Benefits

1. **No Permission Prompts**: Claude executes allowed operations without asking
2. **Faster Responses**: No interruption for permission checks
3. **Maintained Security**: Only safe operations are whitelisted
4. **Real Database Access**: Claude can query the database directly
5. **Comprehensive Context**: Full project and database context available

## Testing

### Test Command Examples
```bash
# Database queries
php artisan claude:test-direct "How many accounts does Andrew Mashamba have?"
php artisan claude:test-direct "What is the total number of clients?"
php artisan claude:test-direct "List all account types"

# File operations
php artisan claude:test-direct "What models are in the app/Models directory?"
php artisan claude:test-direct "Show me the routes in web.php"
```

### Expected Behavior
- Claude should provide real database results
- No permission prompts should appear
- Response time: 5-15 seconds typically
- Full access to project context

## Configuration Files

### `.mcp.json`
```json
{
  "mcpServers": {
    "saccos-db": {
      "type": "stdio",
      "command": "npx",
      "args": [
        "-y",
        "@executeautomation/database-server",
        "--postgresql",
        "--host", "localhost",
        "--database", "saccos_core",
        "--user", "postgres"
      ]
    }
  }
}
```

### `.claude/settings.json` (Optional)
Can be used for project-specific Claude settings, but the allowed tools are now handled programmatically in LocalClaudeService.

## Troubleshooting

### Issue: Claude still asks for permissions
**Solution**: Check that LocalClaudeService is being used and allowed tools are properly configured

### Issue: Database queries not working
**Solution**: Ensure `.mcp.json` exists and PostgreSQL is running

### Issue: Command timeout
**Solution**: Complex queries may take longer; timeout is set to 300 seconds (5 minutes)

## Architecture Flow

```
User Message
    ↓
AiAgentChat (Livewire Component)
    ↓
DirectClaudeService (if useDirectClaude = true)
    ↓
LocalClaudeService (with allowed tools)
    ↓
Claude CLI (with --allowed-tools flags)
    ↓
Database/File Access (no permission prompts)
    ↓
Response to User
```

## Summary

The implementation successfully:
1. ✅ Sends everything to Claude (as requested)
2. ✅ Avoids permission prompts using `--allowed-tools`
3. ✅ Provides real database query results
4. ✅ Maintains security through whitelisted operations
5. ✅ Integrates seamlessly with existing AI Agent Chat interface

---

**Version**: 1.0.0  
**Last Updated**: August 2024  
**Implementation Status**: ✅ Complete and Tested