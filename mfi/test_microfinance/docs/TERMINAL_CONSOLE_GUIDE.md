# Terminal Console Guide - SACCOS Core System

## Overview

The Terminal Console is a powerful web-based terminal that allows you to run console applications, manage Claude Bridge, and execute system commands directly from your browser. This feature provides a seamless way to interact with your SACCOS system without needing external terminal access.

## Features

### ✅ **Core Features**
- **Real-time Command Execution** - Execute commands and see output instantly
- **Claude Bridge Management** - Start, stop, and monitor Claude Bridge directly
- **Command History** - Navigate through previous commands with arrow keys
- **Process Management** - Start and stop long-running processes
- **Quick Actions** - One-click buttons for common operations
- **Responsive Design** - Works on desktop, tablet, and mobile devices

### ✅ **Security Features**
- **Authentication Required** - Only authenticated users can access
- **Command Logging** - All commands are logged for audit purposes
- **Process Isolation** - Commands run in isolated processes
- **Input Validation** - Commands are validated before execution

## Accessing the Terminal

### **URL**
```
http://your-domain.com/terminal
```

### **Navigation**
1. Log in to your SACCOS system
2. Navigate to `/terminal` in your browser
3. The terminal console will load with a modern interface

## Interface Overview

### **Terminal Header**
- **Window Controls** - Red, yellow, green dots (macOS style)
- **Title** - "Terminal Console - SACCOS Core System"
- **Claude Bridge Status** - Shows if Claude Bridge is active/inactive
- **Quick Controls** - Clear, Info, Stop Process buttons

### **Terminal Output**
- **Black background** with green text (classic terminal look)
- **Scrollable** output area
- **Auto-scroll** to bottom when new output arrives
- **Monospace font** for proper command display

### **Command Input**
- **Green prompt** ($) indicating ready for input
- **Command history** navigation with arrow keys
- **Enter key** to execute commands
- **Disabled** when a process is running

### **Quick Actions**
Four panels with common commands:

#### **🤖 Claude Bridge**
- Start Bridge
- Test Connection
- Stop Bridge

#### **💻 System**
- Laravel Version
- PHP Version
- List Directory

#### **🏦 SACCOS**
- Test Claude
- Migration Status
- Routes

#### **📁 Files**
- List Zona AI
- List Bridge Files
- View Logs

## Common Commands

### **Claude Bridge Management**

```bash
# Start Claude Bridge
./zona_ai/start_claude_bridge.sh

# Test Claude connection
php artisan claude:test-local "How many members?"

# Write Claude response
php zona_ai/write_response.php req_123456 "Your response here"

# Stop Claude Bridge
pkill -f claude_code_bridge_proper
```

### **System Information**

```bash
# Laravel version
php artisan --version

# PHP version
php --version

# System info
php artisan terminal:test

# Current directory
pwd

# List files
ls -la
```

### **SACCOS System**

```bash
# Database migrations
php artisan migrate:status

# List routes
php artisan route:list --compact

# Clear cache
php artisan cache:clear

# View logs
tail -f storage/logs/laravel.log
```

### **File Operations**

```bash
# List Zona AI files
ls -la zona_ai/

# View Claude Bridge files
ls -la storage/app/claude-bridge/

# Monitor logs
tail -f zona_ai/zona.log

# Check processes
ps aux | grep claude
```

## Claude Bridge Integration

### **Starting Claude Bridge**
1. Click "Start Bridge" in the Claude Bridge panel
2. The terminal will show the bridge startup process
3. Status will change to "Active" with PID
4. Bridge will start monitoring for requests

### **Testing Claude Bridge**
1. Click "Test Connection" in the Claude Bridge panel
2. Terminal will execute a test command
3. Results will show in the output area
4. Success/failure status will be displayed

### **Managing Claude Bridge**
- **Start** - Initializes the bridge and starts monitoring
- **Stop** - Terminates the bridge process
- **Status** - Shows current bridge status (Active/Inactive)
- **PID** - Displays the process ID when active

## Advanced Usage

### **Command History**
- **Up Arrow** - Navigate to previous commands
- **Down Arrow** - Navigate to newer commands
- **History** - Shows count of saved commands
- **Auto-save** - Commands are automatically saved

### **Process Management**
- **Long-running processes** can be started and monitored
- **Stop Process** button appears when a process is running
- **Process ID** tracking for management
- **Background execution** for non-blocking operations

### **Directory Navigation**
- **Current directory** is displayed in status bar
- **Change directory** with `cd` command
- **List directory** with `ls` command
- **Path tracking** for file operations

## Security Considerations

### **Access Control**
- **Authentication required** - Only logged-in users can access
- **User tracking** - All commands are logged with user ID
- **Session management** - Commands are tied to user sessions

### **Command Restrictions**
- **Safe commands** - Only safe system commands are allowed
- **Path restrictions** - Commands are limited to project directory
- **Process limits** - Maximum execution time limits

### **Audit Trail**
- **Command logging** - All commands are logged
- **User identification** - Commands are tied to specific users
- **Timestamp tracking** - All activities are timestamped

## Troubleshooting

### **Common Issues**

#### **Terminal Not Loading**
```bash
# Check if Livewire is working
php artisan livewire:publish --assets

# Clear cache
php artisan cache:clear
php artisan config:clear
```

#### **Commands Not Executing**
```bash
# Check permissions
chmod +x zona_ai/*.sh
chmod +x zona_ai/*.php

# Check PHP process
php --version
```

#### **Claude Bridge Issues**
```bash
# Check if bridge is running
ps aux | grep claude

# Restart bridge
pkill -f claude_code_bridge_proper
./zona_ai/start_claude_bridge.sh

# Check logs
tail -f zona_ai/zona.log
```

### **Error Messages**

#### **"Permission Denied"**
- Check file permissions
- Ensure user has execute rights
- Verify file ownership

#### **"Command Not Found"**
- Check if command exists in PATH
- Verify command syntax
- Ensure command is available on system

#### **"Process Timeout"**
- Command is taking too long
- Check system resources
- Consider breaking into smaller commands

## Best Practices

### **Command Execution**
1. **Test commands** in small batches
2. **Use quotes** for commands with spaces
3. **Check output** before proceeding
4. **Stop processes** when done

### **Claude Bridge Management**
1. **Start bridge** before testing
2. **Monitor logs** for issues
3. **Stop bridge** when not needed
4. **Test connection** regularly

### **File Operations**
1. **Use absolute paths** when possible
2. **Check permissions** before operations
3. **Backup files** before modifications
4. **Monitor disk space**

## API Reference

### **Livewire Component**
```php
// Component class
App\Http\Livewire\Terminal\TerminalConsole

// Available methods
executeCommand($command)
stopProcess($processId)
clearOutput()
startClaudeBridge()
stopClaudeBridge()
testClaudeBridge()
```

### **Events**
```javascript
// Output updated
Livewire.emit('outputUpdated')

// Terminal cleared
Livewire.emit('terminalCleared')

// Process started
Livewire.emit('processStarted', processId)

// Process stopped
Livewire.emit('processStopped', processId)
```

## Configuration

### **Environment Variables**
```env
# Terminal settings
TERMINAL_MAX_OUTPUT_LINES=1000
TERMINAL_AUTO_SCROLL=true
TERMINAL_THEME=dark
TERMINAL_FONT_SIZE=14px
```

### **Cache Settings**
```php
// Command history cache
Cache::put("terminal_history_" . Auth::id(), $history, 86400);

// Process tracking cache
Cache::put("terminal_process_{$processId}", $info, 3600);
```

## Support

### **Getting Help**
1. **Check logs** - Review terminal and system logs
2. **Test commands** - Use simple commands first
3. **Restart services** - Restart terminal and bridge
4. **Contact support** - For persistent issues

### **Log Files**
- **Terminal logs** - `storage/logs/laravel.log`
- **Claude logs** - `zona_ai/zona.log`
- **Bridge logs** - `storage/app/claude-bridge/`

### **Monitoring**
- **Process status** - Check running processes
- **Resource usage** - Monitor CPU and memory
- **Error rates** - Track command failures
- **User activity** - Monitor terminal usage

---

**Note**: This terminal console provides powerful system access. Use responsibly and ensure only authorized users have access to this feature.
