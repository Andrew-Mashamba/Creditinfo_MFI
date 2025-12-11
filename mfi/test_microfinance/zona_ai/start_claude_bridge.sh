#!/bin/bash

# Claude Code Bridge Startup Script
# This script starts the proper bridge that waits for Claude Code to respond

echo "Starting Claude Code Bridge (Proper Implementation)..."
echo "This bridge will provide context to Claude Code and wait for responses."
echo "Make sure Claude Code is monitoring this terminal for questions."
echo ""

# Kill any existing bridge processes
pkill -f "zona_enhanced.php"
pkill -f "claude_code_bridge.php"
pkill -f "claude_bridge_context_only.php"
pkill -f "zona_context_provider.php"

echo "✓ Stopped any existing auto-responders"
echo ""

# Start the proper bridge
echo "Starting proper bridge..."
php zona_ai/claude_code_bridge_proper.php
