# Local Claude Code CLI Streaming Implementation

## Overview
This document explains how the AI streaming process works with the local Claude Code CLI (Claude Desktop) using MCP tools for direct database access.

## Architecture

```
User → Frontend → /ai/process → LocalClaudeService → Claude CLI → Cache → SSE → Frontend
```

## Implementation Details

### 1. Frontend Initiates Streaming
```javascript
// Frontend sends message to /ai/process
fetch('/ai/process', {
    method: 'POST',
    body: JSON.stringify({
        message: userMessage,
        sessionId: sessionId
    })
})

// Then opens SSE connection
const eventSource = new EventSource(`/ai/stream/${sessionId}`)
```

### 2. Backend Processing Chain

#### Primary: LocalClaudeService (Uses Claude Code CLI)
```php
// StreamController::processMessageAsync()
$context = [
    'enable_streaming' => true,
    'stream_to_session' => $sessionId,
    'session_id' => $sessionId,
    'format' => 'html'
];

$response = $this->localClaudeService->sendMessage($message, $context);
```

#### Secondary: ClaudeCliService (Direct CLI)
```php
$response = $this->claudeCliService->sendMessage($message, [
    'session_id' => $sessionId,
    'flags' => []
]);
```

#### Tertiary: Simulated Streaming
```php
$this->startImmediateStreaming($message, $sessionId, $streamKey, $completeKey);
```

### 3. Local Claude Integration

#### ClaudeProcessManager
- Maintains persistent Claude process
- Preserves context between messages
- Manages pipes for stdin/stdout/stderr

#### LocalClaudeService Features
- Streaming callback support
- Session management
- Context preservation
- MCP tool integration

### 4. Streaming Flow

1. **Message Processing**
   ```php
   // LocalClaudeService::sendWithPersistentProcess()
   $context['stream_callback'] = function($chunk) use ($context) {
       if (isset($context['stream_to_session'])) {
           $this->streamToSession($context['stream_to_session'], $chunk);
       }
   };
   ```

2. **Cache Updates**
   ```php
   // LocalClaudeService::streamToSession()
   $streamKey = "claude_stream_{$sessionId}";
   $currentStream = Cache::get($streamKey, '');
   Cache::put($streamKey, $currentStream . $chunk, 60);
   ```

3. **SSE Delivery**
   ```php
   // StreamController::stream()
   while (true) {
       $content = Cache::get($streamKey, '');
       if (strlen($content) > $lastPosition) {
           $newChunk = substr($content, $lastPosition);
           echo "event: message\n";
           echo "data: " . json_encode(['chunk' => $newChunk]) . "\n\n";
           ob_flush();
           flush();
       }
   }
   ```

### 5. MCP Database Access

Claude Code CLI has direct database access via MCP tools:

```json
{
  "mcpServers": {
    "saccos-database": {
      "command": "npx",
      "args": [
        "-y",
        "@executeautomation/database-server",
        "--postgresql",
        "--database", "saccos_core_system"
      ]
    }
  }
}
```

### 6. Available MCP Tools for Claude

- `list-tables` - Get all database tables
- `describe-table` - View table schema  
- `query` - Execute SELECT queries
- `get-schema` - Get database schema

### 7. Event Broadcasting (Optional)

```php
// ClaudeStreamUpdate event for WebSocket support
event(new \App\Events\ClaudeStreamUpdate($sessionId, $chunk));
```

## Service Priority

1. **LocalClaudeService** - Uses persistent Claude process with MCP
2. **ClaudeCliService** - Direct CLI execution per request
3. **ClaudeService** - Remote Claude API (requires API key)
4. **Simulated** - Fallback for testing

## Key Files

- `/app/Services/LocalClaudeService.php` - Main local Claude integration
- `/app/Services/ClaudeProcessManager.php` - Persistent process management
- `/app/Services/ClaudeCliService.php` - Direct CLI execution
- `/app/Http/Controllers/StreamController.php` - SSE streaming controller
- `/app/Events/ClaudeStreamUpdate.php` - WebSocket event
- `/routes/web.php` - Streaming routes

## Routes

```php
Route::post('/ai/process', [StreamController::class, 'process']);
Route::get('/ai/stream/{sessionId}', [StreamController::class, 'stream']);
Route::post('/ai/stream/{sessionId}/complete', [StreamController::class, 'complete']);
```

## Testing the Implementation

### 1. Check Claude CLI Installation
```bash
which claude
# Should return: /usr/local/bin/claude or similar
```

### 2. Test Direct CLI
```bash
claude "Hello, are you connected?"
```

### 3. Test via Artisan
```bash
php artisan test:local-claude-connection
php artisan test:direct-claude "What is 2+2?"
```

### 4. Test Streaming in Browser
1. Visit `/ai-agent`
2. Type "hi" and send
3. Watch response stream in word-by-word

## Configuration

### .env Variables
```env
CLAUDE_CLI_PATH=/usr/local/bin/claude
CLAUDE_USE_LOCAL=true
CLAUDE_ENABLE_STREAMING=true
```

### MCP Configuration (.mcp.json)
```json
{
  "tools": [
    "query",
    "describe-table",
    "list-tables",
    "get-schema"
  ],
  "database": {
    "type": "postgresql",
    "host": "localhost",
    "database": "saccos_core_system",
    "user": "postgres",
    "port": 5432
  }
}
```

## Troubleshooting

### Issue: Claude CLI not found
```bash
# Install Claude Desktop from: https://claude.ai/download
# Ensure claude CLI is in PATH
export PATH="$PATH:/Applications/Claude.app/Contents/MacOS"
```

### Issue: Streaming not working
1. Check Cache driver is configured (Redis recommended)
2. Verify SSE headers are not being buffered by web server
3. Check browser console for EventSource errors

### Issue: MCP tools not available
1. Restart Claude Desktop after configuration
2. Check ~/.config/Claude/claude_desktop_config.json
3. Verify database credentials in MCP config

## Performance Considerations

- Persistent process reduces latency (no startup overhead)
- Streaming provides better UX for long responses
- Cache-based approach allows for horizontal scaling
- MCP direct database access eliminates round trips

## Security Notes

- Claude CLI runs with user permissions
- Database access is read-only via MCP
- Session IDs should be cryptographically secure
- Sanitize all user input before CLI execution