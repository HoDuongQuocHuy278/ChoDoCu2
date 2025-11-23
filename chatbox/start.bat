@echo off
echo ========================================
echo   Chatbot Cho Do Cu - Starting...
echo ========================================
echo.

REM Check if virtual environment exists
if not exist "venv\" (
    echo Creating virtual environment...
    python -m venv venv
)

REM Activate virtual environment
echo Activating virtual environment...
call venv\Scripts\activate.bat

REM Install/update dependencies
echo Installing dependencies...
pip install -r requirements.txt --quiet

REM Download NLTK data if needed
echo Checking NLTK data...
python -c "import nltk; nltk.download('punkt_tab', quiet=True); nltk.download('punkt', quiet=True)" 2>nul

REM Check if model exists, if not train it
if not exist "data.pth" (
    echo Model not found. Training model...
    python train.py
    echo.
)

REM Start Flask server
echo.
echo ========================================
echo   Starting Chatbot API Server...
echo ========================================
echo   URL: http://127.0.0.1:5000
echo   Press Ctrl+C to stop
echo ========================================
echo.

python app.py

pause

