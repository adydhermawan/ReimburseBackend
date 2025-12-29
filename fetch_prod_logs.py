import ftplib
import sys
import os

# FTP Credentials from .env.production
FTP_HOST = "ftpupload.net"
FTP_USER = "if0_40730583"
FTP_PASS = "jarangN0NT0N"
FTP_PORT = 21

REMOTE_LOG_PATH = "recashly.dadi.web.id/htdocs/storage/logs/laravel.log"
LOCAL_LOG_PATH = "prod_laravel.log"

def fetch_logs():
    print(f"Connecting to {FTP_HOST}...")
    try:
        ftp = ftplib.FTP()
        ftp.connect(FTP_HOST, FTP_PORT)
        ftp.login(FTP_USER, FTP_PASS)
        print("Logged in successfully.")
        
        # Check if file exists first to avoid crashing
        try:
             size = ftp.size(REMOTE_LOG_PATH)
             print(f"Log file found, size: {size} bytes")
        except:
             print("Log file not found or inaccessible.")
             return

        print(f"Downloading {REMOTE_LOG_PATH} to {LOCAL_LOG_PATH}...")
        
        with open(LOCAL_LOG_PATH, 'wb') as f:
            ftp.retrbinary(f"RETR {REMOTE_LOG_PATH}", f.write)
            
        print("Download complete.")
        ftp.quit()
        
    except Exception as e:
        print(f"Error: {e}")
        sys.exit(1)

if __name__ == "__main__":
    fetch_logs()
