# MCP Database Server Setup Guide

## Overview

This guide explains how to set up the MCP (Model Context Protocol) Database Server to enable Claude Code CLI to directly query your SACCOS PostgreSQL database.

## What is MCP Database Server?

The [MCP Database Server](https://github.com/executeautomation/mcp-database-server) is a Model Context Protocol server that allows Claude Code CLI to directly interact with databases using tools like:

- `read_query`: Execute SELECT queries
- `write_query`: Execute INSERT, UPDATE, DELETE queries
- `list_tables`: Get all table names
- `describe_table`: View table schema
- `export_query`: Export results as CSV/JSON
- And more...

## Prerequisites

1. **Node.js 18+** installed on your system
2. **Claude Code CLI** installed and configured
3. **PostgreSQL** database running (your SACCOS database)
4. **Laravel** application with database access

## Installation Steps

### 1. Install MCP Database Server

```bash
# Install globally
npm install -g @executeautomation/database-server

# Or install locally in your project
npm install @executeautomation/database-server
```

### 2. Verify Installation

```bash
# Test the installation
npx @executeautomation/database-server --help
```

### 3. Test Database Connection

```bash
# Test with your PostgreSQL database
npx @executeautomation/database-server \
  --postgresql \
  --host localhost \
  --database saccos_core_system \
  --user postgres \
  --password your_password \
  --port 5432
```

## Configuration

### 1. Claude Desktop Configuration

Add the following configuration to your Claude Desktop config file:

**Config File Locations:**
- **macOS**: `~/Library/Application Support/Claude/claude_desktop_config.json`
- **Windows**: `%APPDATA%\Claude\claude_desktop_config.json`
- **Linux**: `~/.config/Claude/claude_desktop_config.json`

**Configuration:**
```json
{
  "mcpServers": {
    "saccos-database": {
      "command": "npx",
      "args": [
        "-y",
        "@executeautomation/database-server",
        "--postgresql",
        "--host", "localhost",
        "--database", "saccos_core_system",
        "--user", "postgres",
        "--password", "your_password_here",
        "--port", "5432",
        "--ssl", "false"
      ]
    }
  }
}
```

### 2. Environment Variables (Optional)

For better security, you can use environment variables:

```bash
export DB_HOST=localhost
export DB_NAME=saccos_core_system
export DB_USER=postgres
export DB_PASSWORD=your_password
export DB_PORT=5432
```

Then update your config:
```json
{
  "mcpServers": {
    "saccos-database": {
      "command": "npx",
      "args": [
        "-y",
        "@executeautomation/database-server",
        "--postgresql",
        "--host", "${DB_HOST}",
        "--database", "${DB_NAME}",
        "--user", "${DB_USER}",
        "--password", "${DB_PASSWORD}",
        "--port", "${DB_PORT}"
      ]
    }
  }
}
```

## Testing the Integration

### 1. Test with Claude Code CLI

```bash
# Test basic functionality
claude "List all tables in the database"

# Test specific queries
claude "How many users are in the system?"

# Test table schema
claude "Show me the schema for the clients table"

# Test data queries
claude "What are the total savings across all accounts?"
```

### 2. Test with Laravel Integration

Run the test script:
```bash
php test_mcp_database_server.php
```

Or use the chat interface test buttons:
- **Test MCP**: Tests MCP Database Service status
- **Test Query**: Tests database query execution
- **MCP Config**: Shows configuration instructions

## Available MCP Tools

| Tool | Description | Example Usage |
|------|-------------|---------------|
| `list_tables` | Get all table names | `list_tables` |
| `describe_table` | View table schema | `describe_table "clients"` |
| `read_query` | Execute SELECT queries | `read_query "SELECT COUNT(*) FROM users"` |
| `write_query` | Execute INSERT/UPDATE/DELETE | `write_query "UPDATE users SET status='active'"` |
| `export_query` | Export results as CSV/JSON | `export_query "SELECT * FROM clients" "csv"` |
| `create_table` | Create new tables | `create_table "CREATE TABLE test..."` |
| `alter_table` | Modify table schema | `alter_table "ALTER TABLE users ADD COLUMN..."` |
| `drop_table` | Remove tables | `drop_table "test_table"` |
| `append_insight` | Add business insights | `append_insight "High member growth rate"` |
| `list_insights` | List all insights | `list_insights` |

## Usage Examples

### Basic Queries

```bash
# Count users
claude "How many users are in the system?"

# List active members
claude "Show me all active members"

# Get account balances
claude "What are the total savings across all accounts?"
```

### Schema Exploration

```bash
# List all tables
claude "List all tables in the database"

# Describe specific table
claude "Show me the schema for the loans table"

# Explore relationships
claude "What are the relationships between clients and accounts?"
```

### Data Analysis

```bash
# Financial analysis
claude "What is the total loan portfolio value?"

# Member statistics
claude "How many new members joined this month?"

# Transaction analysis
claude "Show me the transaction history for the last 30 days"
```

### Data Export

```bash
# Export member list
claude "Export the member list as CSV"

# Export financial report
claude "Export loan applications as JSON"
```

## Troubleshooting

### Common Issues

1. **Connection Failed**
   - Verify PostgreSQL is running
   - Check database credentials
   - Ensure firewall allows connections

2. **Permission Denied**
   - Verify database user has proper permissions
   - Check SSL settings if required

3. **MCP Server Not Found**
   - Ensure MCP Database Server is installed
   - Check Node.js version (requires 18+)
   - Verify npx is available

### Debug Commands

```bash
# Test database connection directly
psql -h localhost -U postgres -d saccos_core_system

# Test MCP server manually
npx @executeautomation/database-server --postgresql --host localhost --database saccos_core_system --user postgres --password your_password

# Check Claude Desktop logs
# Look for MCP-related errors in Claude Desktop
```

## Security Considerations

1. **Database Credentials**: Store securely, use environment variables
2. **Network Security**: Use SSL for remote connections
3. **User Permissions**: Limit database user to read-only if possible
4. **Query Validation**: Implement query validation for write operations

## Integration with Laravel

The MCP Database Server is integrated with your Laravel application through:

- `McpDatabaseService`: Manages MCP server operations
- `LocalClaudeService`: Enhanced with MCP capabilities
- Test scripts and UI buttons for verification

## Next Steps

1. **Test the integration** using the provided test scripts
2. **Configure Claude Desktop** with the MCP server
3. **Start using Claude Code CLI** for database queries
4. **Explore advanced features** like data export and insights

## Resources

- [MCP Database Server GitHub](https://github.com/executeautomation/mcp-database-server)
- [MCP Database Server Documentation](https://executeautomation.github.io/mcp-database-server/)
- [Claude Code CLI Reference](https://docs.anthropic.com/en/docs/claude-code/cli-reference)
- [Model Context Protocol](https://modelcontextprotocol.io/)
