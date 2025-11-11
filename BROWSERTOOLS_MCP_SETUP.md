# BrowserTools MCP Setup Guide

## Installation Complete ✓

The BrowserTools MCP server has been successfully configured and is ready to use!

## What's Been Set Up

1. **MCP Server Configuration**: Added to Cline settings at `github.com/AgentDeskAI/browser-tools-mcp`
2. **Browser Tools Server**: Started in a separate terminal window (middleware for Chrome extension)

## Next Steps: Install Chrome Extension

To complete the setup, you need to install the Chrome extension:

### Installation Steps:

1. **Download the extension**:
   - Go to: https://github.com/AgentDeskAI/browser-tools-mcp/releases/download/v1.2.0/BrowserTools-1.2.0-extension.zip
   - Download and extract the ZIP file

2. **Load the extension in Chrome**:
   - Open Chrome and navigate to `chrome://extensions/`
   - Enable "Developer mode" (toggle in top-right corner)
   - Click "Load unpacked"
   - Select the extracted BrowserTools folder

3. **Verify Installation**:
   - Open any webpage in Chrome
   - Open Chrome DevTools (F12 or right-click → Inspect)
   - Look for the "BrowserToolsMCP" tab in DevTools
   - The panel should show a connection status

### Troubleshooting

If the connection isn't working:
- Close all Chrome windows completely
- Restart the browser-tools-server terminal (the command window that opened)
- Ensure only ONE DevTools panel is open
- Check that the BrowserToolsMCP panel shows "Connected"

## Available Capabilities

Once connected, you can ask Cline to:

### Debugging Tools
- **Get Console Logs**: "Check the browser console logs"
- **Get Console Errors**: "Show me any console errors"
- **Get Network Errors**: "Are there any network errors?"
- **Get Network Logs**: "Show all network requests"
- **Take Screenshots**: "Take a screenshot of the current page"
- **Get Selected Element**: "Inspect the selected element"
- **Run Debugger Mode**: "Enter debugger mode" (runs all debugging tools)

### Audit Tools
- **Accessibility Audit**: "Run an accessibility audit on this page"
- **Performance Audit**: "Check the performance of this page"
- **SEO Audit**: "Run an SEO audit"
- **Best Practices Audit**: "Check best practices on this page"
- **NextJS Audit**: "Run a NextJS audit" (for NextJS applications)
- **Run Audit Mode**: "Enter audit mode" (runs all audit tools)

### Advanced Features
- **Auto-Paste to Cursor**: Enable in DevTools panel to automatically paste screenshots
- **Wipe Logs**: "Clear all browser logs from memory"

## Architecture

```
┌─────────────┐     ┌──────────────┐     ┌───────────────┐     ┌─────────────┐
│    Cline    │ ──► │  MCP Server  │ ──► │  Node Server  │ ──► │   Chrome    │
│  (Cursor)   │ ◄── │  (Protocol   │ ◄── │ (Middleware)  │ ◄── │  Extension  │
└─────────────┘     └──────────────┘     └───────────────┘     └─────────────┘
```

All logs are stored locally on your machine and NEVER sent to any third-party service.

## Testing the Setup

Once the Chrome extension is installed and connected, try asking Cline:
- "Take a screenshot of my current browser tab"
- "Check if there are any console errors"
- "Run an accessibility audit on this page"
