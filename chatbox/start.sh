#!/bin/bash

echo "========================================"
echo "  Chatbot Cho Do Cu - Starting..."
echo "========================================"
echo ""

# Check if virtual environment exists
if [ ! -d "venv" ]; then
    echo "Creating virtual environment..."
    python3 -m venv venv
fi

# Activate virtual environment
echo "Activating virtual environment..."
source venv/bin/activate

# Install/update dependencies
echo "Installing dependencies..."
pip install -r requirements.txt --quiet

# Download NLTK data if needed
echo "Checking NLTK data..."
python3 -c "import nltk; nltk.download('punkt_tab', quiet=True); nltk.download('punkt', quiet=True)" 2>/dev/null

# Check if model exists, if not train it
if [ ! -f "data.pth" ]; then
    echo "Model not found. Training model..."
    python3 train.py
    echo ""
fi

# Start Flask server
echo ""
echo "========================================"
echo "  Starting Chatbot API Server..."
echo "========================================"
echo "  URL: http://127.0.0.1:5000"
echo "  Press Ctrl+C to stop"
echo "========================================"
echo ""

python3 app.py

