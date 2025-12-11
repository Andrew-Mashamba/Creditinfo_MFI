# Claude Code Bridge - Proper Setup Guide

## Problem Solved
The previous setup was providing auto-responses instead of properly bridging to Claude Code. This new setup ensures that:
1. Questions are sent to `storage/app/claude-bridge/requests/`
2. The bridge provides context to Claude Code
3. Claude Code writes responses to `storage/app/claude-bridge/responses/`
4. Laravel reads and displays the responses

## Quick Start

### 1. Stop All Auto-Responders
```bash
# Kill any existing processes
pkill -f "zona_enhanced.php"
pkill -f "claude_code_bridge.php"
pkill -f "claude_bridge_context_only.php"
pkill -f "zona_context_provider.php"
```

### 2. Start the Proper Bridge
```bash
# Option 1: Use the startup script
./zona_ai/start_claude_bridge.sh

# Option 2: Start directly
php zona_ai/claude_code_bridge_proper.php
```

### 3. Monitor the Bridge
The bridge will show:
- User questions from Laravel
- Database context gathered
- Instructions for Claude Code
- Waiting status for Claude Code response

## How It Works

### Request Flow
1. **Laravel** → Writes question to `storage/app/claude-bridge/requests/<id>.json`
2. **Bridge** → Reads question, gathers database context
3. **Bridge** → Displays question + context for Claude Code
4. **Claude Code** → Reads the question and context from terminal
5. **Claude Code** → Writes response to `storage/app/claude-bridge/responses/<id>.json`
6. **Laravel** → Reads response and displays to user

### Example Bridge Output
```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📨 New Request from Laravel
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Request ID: req_68a9819159c5b3.21594359
User: Andrew S. Mashamba
Timestamp: 2025-01-23T10:30:00.000000Z

User Question:
How many members does the system have?

📊 Database Context Gathered:
**Current Database Statistics:**
• Total Members/Clients: 3
• Active Members: 3
• System Users: 2
...

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🤖 CLAUDE CODE - PLEASE ANSWER THIS QUESTION:

**User Question:** "How many members does the system have?"

**User:** Andrew S. Mashamba

**Database Context:**
**Current Database Statistics:**
• Total Members/Clients: 3
...

**Instructions for Claude Code:**
1. You have full access to the SACCOS Core System at: /Volumes/DATA/PROJECTS/SACCOS/SYSTEMS/SACCOS_CORE_SYSTEM
2. Use the database context above along with your knowledge of the codebase
3. Provide a comprehensive, accurate answer based on real data
...

**IMPORTANT:** You must write your answer to: /Volumes/DATA/PROJECTS/SACCOS/SYSTEMS/SACCOS_CORE_SYSTEM/storage/app/claude-bridge/responses/req_68a9819159c5b3.21594359.json

⏳ Waiting for Claude Code to respond...
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

## For Claude Code

### When You See a Question:
1. **Read the question and context carefully**
2. **Use the database context provided**
3. **Write your response using the response writer script**

### Writing Responses
Use the response writer script:
```bash
php zona_ai/write_response.php <request_id> "Your response message here"
```

**Example:**
```bash
php zona_ai/write_response.php req_68a9819159c5b3.21594359 "Based on the database context, there are 3 members registered in the SACCOS system. All 3 members are currently active."
```

### Response Format
Your response should be:
- **Accurate** - Based on the database context provided
- **Helpful** - Provide useful information
- **Professional** - Use appropriate tone for a business system
- **Complete** - Answer the question fully

## Testing the Setup

### 1. Test the Bridge
```bash
# Start the bridge
./zona_ai/start_claude_bridge.sh

# In another terminal, test with Laravel
php artisan claude:test-local "How many members does the system have?"
```

### 2. Check Request Files
```bash
# Check if requests are being written
ls -la storage/app/claude-bridge/requests/

# Check if responses are being written
ls -la storage/app/claude-bridge/responses/
```

### 3. Monitor Logs
```bash
# Watch the bridge output
tail -f zona_ai/zona.log
```

## Troubleshooting

### Bridge Not Responding
```bash
# Check if bridge is running
ps aux | grep claude_code_bridge_proper

# Restart the bridge
./zona_ai/start_claude_bridge.sh
```

### No Requests Appearing
```bash
# Check Laravel service
php artisan claude:test-local "test"

# Check directories exist
ls -la storage/app/claude-bridge/
```

### Timeout Errors
- Ensure Claude Code is monitoring the terminal
- Check that you're writing responses to the correct file
- Verify the request ID matches exactly

### Permission Issues
```bash
# Fix permissions
chmod +x zona_ai/*.php
chmod +x zona_ai/*.sh
chmod -R 755 storage/app/claude-bridge/
```

## File Structure
```
zona_ai/
├── claude_code_bridge_proper.php    # Main bridge script
├── write_response.php               # Response writer for Claude Code
├── start_claude_bridge.sh           # Startup script
├── CLAUDE_BRIDGE_SETUP.md           # This guide
└── ...

storage/app/claude-bridge/
├── requests/                        # Laravel writes questions here
├── responses/                       # Claude Code writes answers here
└── claude-monitor.active           # Bridge status marker
```

## Success Indicators

✅ **Bridge is working when:**
- You see "Claude Code Bridge - Proper Implementation" on startup
- Questions appear in the terminal with context
- No auto-responses are generated
- Claude Code can write responses that appear in Laravel

❌ **Bridge is NOT working when:**
- You get immediate auto-responses
- No questions appear in the terminal
- Timeout errors occur
- Responses don't appear in Laravel

## Next Steps

1. **Start the bridge**: `./zona_ai/start_claude_bridge.sh`
2. **Monitor the terminal** for questions
3. **Write responses** using the response writer script
4. **Test with Laravel** to verify the flow works

This setup ensures that Claude Code gets real questions with database context and can provide accurate, helpful responses based on the actual SACCOS system data.
