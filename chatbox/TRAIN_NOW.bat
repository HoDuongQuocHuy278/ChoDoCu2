@echo off
echo ========================================
echo   Training Chatbot Model...
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

REM Train model
echo.
echo Starting training...
echo This may take a few minutes...
echo.
python train.py

echo.
echo ========================================
echo   Training Complete!
echo ========================================
echo   File data.pth has been saved.
echo   You can now run the chatbot with:
echo   python app.py
echo ========================================
echo.

pause

