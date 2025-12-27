#!/bin/bash
# Start local PHP server for testing

echo "🚀 Starting P4PDF Local Server..."
echo ""
echo "📝 Make sure you've installed dependencies first:"
echo "   composer install"
echo ""
echo "🌐 Server will start at: http://localhost:8000"
echo "📄 Open in browser: http://localhost:8000/public/index.html"
echo ""
echo "Press Ctrl+C to stop the server"
echo ""

php -S localhost:8000 -t .

